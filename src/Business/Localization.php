<?php

namespace Stentle\LaravelWebcore\Business;

use DrewM\MailChimp\MailChimp;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Stentle\LaravelWebcore\Models\Cart;
use Stentle\LaravelWebcore\Models\Country;

class Localization
{


    static public function getCountryRegionActive()
    {
        if (isset($_COOKIE['X-Country-Code'])) {
            return $_COOKIE['X-Country-Code'];
        } else if (isset($_COOKIE['X-Region'])) {
            return $_COOKIE['X-Region'];
        } else
            return env('XCODE_DEFAULT','it');
    }


    static public function countries()
    {
        return (new Country())->all();
    }

    static public function setCountry($region, $country)
    {
        setcookie("X-Country-Code", $country, time() + env('SESSION_DURATION') * 60, '/');
        $_COOKIE['X-Country-Code'] = $country;
        setcookie("X-Region", $region, time() + env('SESSION_DURATION') * 60, '/');
        $_COOKIE['X-Region'] = $region;
        Cart::switchCartInSession(Localization::getCountryRegionActive());
    }

    static public function updateAutomatic()
    {
        $geo = getGeoByIp(getRemoteIPAddress());

        if (self::isAllowedCountry($geo['geoplugin_countryCode'])) {
            self::setCountry($geo['geoplugin_continentCode'], $geo['geoplugin_countryCode']);
        } else {
            self::setCountry('Europe', 'it');
        }

        if ($geo['geoplugin_countryCode'] == 'it')
            $lan = "it";
        else
            $lan = "en";

        setcookie("locale", $lan, time() + env('SESSION_DURATION') * 60, '/');
        $_COOKIE['locale'] = $lan;
        App::setLocale($lan);

    }


    static public function isAllowedCountry($isoCode)
    {
        $countries = self::countries();
        foreach ($countries as $country) {
            if ($country->$isoCode == $isoCode) {
                return true;
            }
        }
        return true;
    }

    static public function getCountryCode()
    {
        return @$_COOKIE['X-Country-Code'];
    }

    static public function getRegion()
    {
        return @$_COOKIE['X-Region'];
    }

}