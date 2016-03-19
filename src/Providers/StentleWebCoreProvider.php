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
    public function boot(Router $router)
    {
        $this->app->singleton('clienthttp', function () {
            //salvo i cookie mettendomi in ascolto sulle risposte alle chiamate delle api di stentle
            //doc: http://guzzle.readthedocs.org/en/latest/handlers-and-middleware.html
            $stack = new HandlerStack();
            $stack->setHandler(new CurlHandler());
            $stack->push(Middleware::mapResponse(function (ResponseInterface $response) {
                if ($response->hasHeader('Set-Cookie')) {
                    Session::put('cookie', $response->getHeader('Set-Cookie')[0]);
                }
                if ($this->last_request instanceof RequestInterface) {
                    $this->last_request->getBody()->seek(0);
                    Log::info($response->getStatusCode(), ['uri' => $this->last_request->getUri()->getHost() . $this->last_request->getUri()->getPath(), 'method_request' => $this->last_request->getMethod(), 'body_request' => $this->last_request->getBody()->getContents(), 'headers_request' => json_encode($this->last_request->getHeaders()), 'body_response' => $response->getBody()]);
                    $response->getBody()->seek(0);
                }


                return $response;
            }));

            //setto i cookie su ogni richiesta fatta alle chiamate delle api di stentle
            $stack->push(Middleware::mapRequest(function (RequestInterface $request) {
                $this->last_request = $request;

                if(Session::has('cookie')) { //aggiunto alla richieste anche il cookie di autentificazione in caso è presente
                    return $request->withHeader('cookie', Session::get('cookie'));
                }else
                    return $request;

            }));

            $headers=Config::get('stentle.headers');
            $headers['Accept-Language']=@$_COOKIE['locale'];
            $headers['X-Country-Code']= @$_COOKIE['X-Country-Code'];
            $headers['X-Region']=@$_COOKIE['X-Region'];
            return new Client(['handler' => $stack,'http_errors' => true, 'base_uri' => Config::get('stentle.api'), 'headers' => $headers,'cookies' => true]);

        });

        parent::boot($router);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $user = new User();
        $auth = new Authentication($user,$this->app['session.store']);
        $this->app->instance('Stentle\LaravelWebcore\Contracts\Authentication', $auth);

        Blade::directive('showError', function($field) {
            $field=substr($field, 1, -1);
            return '<?php if($errors->has(\''.$field.'\')) echo "<span class=\'error\'>".$errors->first(\''.$field.'\')."</span>"?>';

        });

        $this->mergeConfigFrom(
            __DIR__.'/../config/core.php', 'stentle'
        );
    }

    public function map(Router $router)
    {
    }
}
