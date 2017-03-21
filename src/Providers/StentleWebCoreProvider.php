<?php

namespace Stentle\LaravelWebcore\Providers;


use Stentle\LaravelWebcore\Business\Authentication;
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
            __DIR__ . '/../config/core.php', 'stentle'
        );

        $this->app->singleton('clienthttp', function () {

            //salvo i cookie mettendomi in ascolto sulle risposte alle chiamate delle api di stentle
            //doc: http://guzzle.readthedocs.org/en/latest/handlers-and-middleware.html
            $stack = new HandlerStack();
            $stack->setHandler(new CurlHandler());
            $stack->push(Middleware::mapResponse(function (ResponseInterface $response) {
                if ($response->hasHeader('Set-Cookie')) {
                    Session::put('cookie', $response->getHeader('Set-Cookie')[0]);

                    //retrieve the token from header set-cookie and store in cookie
                    $tmp = explode(';', $response->getHeader('Set-Cookie')[0]);
                    $tmp = explode('=', $tmp[0]);
                    setcookie("token", $tmp[1], time() + env('SESSION_DURATION') * 60, '/');
                    $_COOKIE['token'] = $tmp[1];

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
                //in caso il token di sessione è invalido pulisco la sessione
                if ($response->getStatusCode() == 403 || ($response->getStatusCode() == 401 && strpos($this->last_request->getUri()->getPath(), 'login') === false)) {
                    Authentication::clearAuthSession();
                    abort(403);
                }
                return $response->withHeader('Content-Length', strlen($content));
            }));

            //setto i cookie su ogni richiesta fatta alle chiamate delle api di stentle
            $stack->push(Middleware::mapRequest(function (RequestInterface $request) {
                if (Session::has('cookie')) { //aggiunto alla richieste anche il cookie di autentificazione in caso è presente
                    $this->last_request = $request->withHeader('cookie', Session::get('cookie'));
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

            return new Client(['handler' => $stack, 'http_errors' => true, 'base_uri' => Config::get('stentle.api'), 'headers' => $headers, 'cookies' => true]);

        });


    }

    public function map(Router $router)
    {
    }
}