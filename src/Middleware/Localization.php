<?php

namespace Stentle\LaravelWebcore\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
/**
 * Si occupa di settare il country/region/lingua in base alla paese di appartenenza
 * ATTENZIONE: questo middleare deve essere posto prima di Carts (TODO: rendere i middleware indipendenti)
 * Class Localization
 * @package App\Http\Middleware
 */
class Localization
{


    public function __construct()
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //check COUNTRY/REGION
        if ( (!isset($_COOKIE['X-Country-Code']) && !isset($_COOKIE['X-Region'])) || !isset($_COOKIE['locale']) ) {
            \Stentle\LaravelWebcore\Business\Localization::updateAutomatic();
        }else{
            App::setLocale($_COOKIE['locale']);
        }

        //set languange on request
        if( isset($_GET['lang']) && ($_GET['lang']=='en' || $_GET['lang']='it')){
            setcookie("locale",$_GET['lang'],time()+env('SESSION_DURATION')*60, '/');
            $_COOKIE['locale'] = $_GET['lang'];
            App::setLocale($_COOKIE['locale']);
        }

        //check country/region active

        $country_active = \Stentle\LaravelWebcore\Business\Localization::getCountryRegionActive();

        setcookie('country_active', $country_active, time()+env('SESSION_DURATION')*60, '/');
        $_COOKIE['country_active'] = $country_active;

        //set xdomain
        setcookie('xdomain', env('X_DOMAIN'), time()+env('SESSION_DURATION')*60, '/');
        $_COOKIE['xdomain'] = env('X_DOMAIN');


        return $next($request);
    }



}
