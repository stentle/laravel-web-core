<?php

namespace Stentle\LaravelWebcore\Providers;


use Stentle\LaravelWebcore\Business\Authentication;
use Stentle\LaravelWebcore\Exceptions\Code;
use Stentle\LaravelWebcore\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class StentleWebCoreProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

        parent::boot();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $user = new User();
        $auth = new Authentication($user, $this->app['session.store']);
        $this->app->instance('Stentle\LaravelWebcore\Contracts\Authentication', $auth);

        Blade::directive('showError', function ($field) {
            $field = substr($field, 1, -1);
            return '<?php if($errors->has(\'' . $field . '\')) echo "<span class=\'error\'>".$errors->first(\'' . $field . '\')."</span>"?>';
        });

        $this->mergeConfigFrom(
            __DIR__ . '/../config/core.php',
            'stentle'
        );

        $this->app->singleton('clienthttp', function () {

            //salvo i cookie mettendomi in ascolto sulle risposte alle chiamate delle api di stentle
            //doc: http://guzzle.readthedocs.org/en/latest/handlers-and-middleware.html
            $stack = new HandlerStack();
            $stack->setHandler(new CurlHandler());
            $stack->push(Middleware::mapResponse(function (ResponseInterface $response) {
            $domain = env('STENTLE_COOKIE_DOMAIN', "");

                if ($response->hasHeader('Set-Cookie')) {

                    $cookies = $response->getHeader('Set-Cookie');
                    $counter = 0;
                    $isStentleCookiePresent = false;
                    $isStentleSSCookiePresent = false;
                    $stentleCookie = "";
                    $stentleSSCookie = "";
                    $stentleTmp1 = "";
                    $stentleSSTmp1 = "";
                  
                    foreach ($cookies as $cookie) {
                        $tmp = explode(';', $cookie);
                        $tmp = explode('=', $tmp[0]);

                        switch ($tmp[0]) {
                            case 'stentle':
                                $isStentleCookiePresent = true;
                                $stentleCookie = $response->getHeader('Set-Cookie')[$counter]; 
                                $stentleTmp1 = $tmp[1];
                                break;
                            case 'stentle-ss':
                                $isStentleSSCookiePresent = true;
                                $stentleSSCookie = $response->getHeader('Set-Cookie')[$counter]; 
                                $stentleSSTmp1 = $tmp[1];
                                break;
                        }

                        $counter += 1;
                    }
                    
                    if ($isStentleCookiePresent) {
                        // set stentle cookie + stentle-ss cookie if in the same request
                        Session::put('cookie', $stentleCookie);
                        setcookie("token", $stentleTmp1, time() + env('SESSION_DURATION') * 60, '/');
                        $_COOKIE['token'] = $stentleTmp1;
                        setcookie("stentle", $stentleTmp1, time() + env('SESSION_DURATION') * 60, '/', $domain);
                        $_COOKIE['stentle'] = $stentleTmp1;

                        if ($isStentleSSCookiePresent) {
                            Session::put('cookie_ss', $stentleSSCookie);
                            setcookie("token_ss", $stentleSSTmp1, 0, '/');
                            $_COOKIE['token_ss'] = $stentleSSTmp1;
                            setcookie("stentle-ss", $stentleSSTmp1, 0, '/', $domain);
                            $_COOKIE['stentle-ss'] = $stentleSSTmp1;
                        }
                    }
                    else if ($isStentleSSCookiePresent && !Session::has('cookie_ss')) {
                        // not authenticating, keep existing ss cookie
                        Session::put('cookie_ss', $stentleSSCookie);
                        setcookie("token_ss", $stentleSSTmp1, 0, '/');
                        $_COOKIE['token_ss'] = $stentleSSTmp1;
                        setcookie("stentle-ss", $stentleSSTmp1, 0, '/', $domain);
                        $_COOKIE['stentle-ss'] = $stentleSSTmp1;;
                    }
                }

                $content = $response->getBody()->getContents();
                $response->getBody()->seek(0);
                if ($this->last_request instanceof RequestInterface) {
                    $this->last_request->getBody()->seek(0);
                    $port = $this->last_request->getUri()->getPort();
                    if ($port != null)
                        $port = ':' . $port;
                    else
                        $port = '';

                    Log::info(
                        $response->getStatusCode(),
                        [
                            'uri' => $this->last_request->getUri()->getHost() . $port . $this->last_request->getUri()->getPath(),
                            'method_request' => $this->last_request->getMethod(),
                            'body_request' => $this->last_request->getBody()->getContents(),
                            'headers_request' => $this->last_request->getHeaders(),
                            'body_response' => json_decode($content, true),
                            'headers_response' => $response->getHeaders()
                        ]
                    );
                }
                //in caso il token di sessione è invalido pulisco la sessione -
                //HACK: Quando scade la sessione, attualmente l'api  mi restituisce un codice generico per indicarmi quando una sessione scade, che però può andare in conflitto con il codice errore restituito in altre situazioni
                if ($response->getStatusCode() == 403  && strpos($this->last_request->getUri()->getPath(), 'auth') === false || ($response->getStatusCode() == 401 && strpos($this->last_request->getUri()->getPath(), 'login') === false && strpos($this->last_request->getUri()->getPath(), 'recovery') === false)) {
                    $auth = $this->app->make('Stentle\LaravelWebcore\Contracts\Authentication');
                    $auth->clearAuthSession();
                    abort(403, Code::SESSIONEXPIRED);
                }
                return $response->withHeader('Content-Length', strlen($content));
            }));

            //setto i cookie su ogni richiesta fatta alle chiamate delle api di stentle
            $stack->push(Middleware::mapRequest(function (RequestInterface $request) {
                if (Session::has('cookie')) { //aggiunto alla richieste anche il cookie di autentificazione in caso è presente
                    $header = Session::get('cookie');
                    $request = $request->withHeader('cookie', $header);

                    if (Session::has('cookie_ss')) {
                        $header = Session::get('cookie_ss');
                        $request = $request->withAddedHeader('cookie', $header);
                    }

                    $this->last_request = $request;
                    return $this->last_request;
                } else {
                    $this->last_request = $request;
                    return $request;
                }
            }));

            $headers = Config::get('stentle.headers');
            if (!isset($_COOKIE['locale'])) {
                $locale = env('LOCALE_DEFAULT', 'en');
            } else {
                $locale = $_COOKIE['locale'];
            }


            if (!isset($_COOKIE['X-Country-Code']) && !isset($_COOKIE['X-Region'])) {
                $headers['X-Region'] = env('XREGION_DEFAULT', 'Europe');
            } else if (isset($_COOKIE['X-Country-Code'])) {
                $headers['X-Country-Code'] = $_COOKIE['X-Country-Code'];
            } else {
                $headers['X-Region'] = $_COOKIE['X-Region'];
            }


            $headers['Accept-Language'] = $locale;
            // $headers['Accept'] = 'application/stentle.api-v0.2+json';

            return new Client(['handler' => $stack, 'http_errors' => true, 'base_uri' => Config::get('stentle.api'), 'headers' => $headers, 'cookies' => true]);
        });
    }

    public function map(Router $router)
    {
    }
}
