<?php

namespace Stentle\LaravelWebcore\Middleware;

use Stentle\LaravelWebcore\Business\Authentication;
use Stentle\LaravelWebcore\Models\Cart;
use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

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
            $countries_permits=['it','gb'];
            $geo=getGeoByIp(getRemoteIPAddress());
            if(in_array($geo['geoplugin_countryCode'],$countries_permits)){
                setcookie("X-Country-Code", $geo['geoplugin_countryCode'], time() + env('SESSION_DURATION') * 60, '/');
                $_COOKIE['X-Country-Code'] = $geo['geoplugin_countryCode'];
            }else{
                setcookie("X-Region", "Europe", time()+env('SESSION_DURATION')*60, '/');
                $_COOKIE['X-Region'] = "Europe";
            }

            if($geo['geoplugin_countryCode']=='it')
                $lan="it";
            else
                $lan="en";

            setcookie("locale",$lan,time()+env('SESSION_DURATION')*60, '/');
            $_COOKIE['locale'] = $lan;
            App::setLocale($lan);
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

        $country_active = '';
        if (isset($_COOKIE['X-Country-Code'])) {
            $country_active = $_COOKIE['X-Country-Code'];
        } else if (isset($_COOKIE['X-Region'])) {
            $country_active = $_COOKIE['X-Region'];
        } else
            $country_active = 'gb';

        setcookie('country_active', $country_active, time()+env('SESSION_DURATION')*60, '/');
        $_COOKIE['country_active'] = $country_active;

        //set xdomain
        setcookie('xdomain', env('X_DOMAIN'), time()+env('SESSION_DURATION')*60, '/');
        $_COOKIE['xdomain'] = env('X_DOMAIN');


        return $next($request);
    }

    private function resetCookiesCart()
    {
        setcookie('cart_id', null, time()-3600, '/');
        $_COOKIE['cart_id'] = null;
    }

}
