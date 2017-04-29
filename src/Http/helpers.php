<?php
/**
 * Created by PhpStorm.
 * User: giuseppetoto
 * Date: 16/09/15
 * Time: 16:58
 */

function shipto_html_li($item)
{
    $class = '';
    if (isset($item['x-country-code'])) {
        if (isset($_COOKIE['X-Country-Code']) && $_COOKIE['X-Country-Code'] == $item['x-country-code']) {
            $class = 'selected';
        }
        return '<li data-value="' . $item['x-country-code'] . '" class="item-ship ' . $class . '">' . $item['name'] . '</li>';
    } else if (isset($item['x-region'])) {
        if (isset($_COOKIE['X-Region']) && $_COOKIE['X-Region'] == $item['x-region']) {
            $class = 'selected';
        }
        return '<li data-value="' . $item['x-region'] . '" class="item-ship ' . $class . '">' . $item['name'] . '</li>';
    }

}

function shipto_html_options($item)
{
    $class = '';
    if (isset($item['x-country-code'])) {
        if (isset($_COOKIE['X-Country-Code']) && $_COOKIE['X-Country-Code'] == $item['x-country-code']) {
            $class = 'selected';
        }
        return '<option value="' . $item['x-country-code'] . '" ' . $class . '>' . $item['name'] . '</option>';
    } else if (isset($item['x-region'])) {
        if (isset($_COOKIE['X-Region']) && $_COOKIE['X-Region'] == $item['x-region']) {
            $class = 'selected';
        }
        return '<option value="' . $item['x-region'] . '" ' . $class . '>' . $item['name'] . '</option>';
    }

}

function mapContinentCode($code)
{

    switch (strtoupper($code)) {
        case 'AS':
            return 'Asia';
        case 'EU':
            return 'Europe';
        case 'AF':
            return 'Africa';
        case 'NA':
        case 'SA':
            return 'America';
        default:
            return 'Europe';
    }

}

function getListCountryCode()
{
    $country = json_decode(env('MULTI_CATALOG'), true);
    $o = [];
    foreach ($country as $item) {
        if (isset($item['x-country-code']))
            $o[] = strtolower($item['x-country-code']);
    }
    return $o;
}

function isActiveLanguage($value)
{
    if (isset($_COOKIE['locale']) && $_COOKIE['locale'] == $value)
        return 'active';
}


function getShipToActive(){
    if (isset($_COOKIE['X-Country-Code']))
        return $_COOKIE['X-Country-Code'];
    else    if (isset($_COOKIE['X-Region']))
        return $_COOKIE['X-Region'];
    else
        return env('XREGION_DEFAULT','Europe');
}
function isItemMenuActive($value)
{

    if (strpos($_SERVER['REQUEST_URI'], $value) !== false) {
        return 'active';
    }
}

/**
 * Recupera le informazioni di geolocalizzazione tramite ip
 * EXAMPLE:
 *  $city = $geo["geoplugin_city"];
 * $region = $geo["geoplugin_regionName"];
 * $country = $geo["geoplugin_countryName"];
 * @return mixed
 */
function getGeoByIp($ip)
{
    if (isset($_SERVER['GEOIP_CITY_COUNTRY_CODE']) && isset($_SERVER['GEOIP_CITY_CONTINENT_CODE'])) {
        $geo['geoplugin_countryCode'] = strtolower($_SERVER['GEOIP_CITY_COUNTRY_CODE']);
        $geo['geoplugin_continentCode'] = strtolower($_SERVER['GEOIP_CITY_CONTINENT_CODE']);
    } else {
        try {
            $geo = unserialize(file_get_contents("http://www.geoplugin.net/php.gp?ip=$ip"));
        } catch (Exception $e) {
            $geo['geoplugin_countryCode'] = env('XCODE_DEFAULT','it');
            $geo['geoplugin_continentCode'] = env('XREGION_DEFAULT','Europe');
        }
        $geo['geoplugin_countryCode'] = strtolower($geo['geoplugin_countryCode']);
        if (!isset($geo['geoplugin_continentCode'])) {
            $geo['geoplugin_continentCode'] = null;
        }
    }

    switch (strtolower($geo['geoplugin_continentCode'])) { //converto il code continent in modo esteso
        case 'eu':
        case 'europe':
            $geo['geoplugin_continentCode'] = 'Europe';
            break;
        case 'as':
        case 'asia':
            $geo['geoplugin_continentCode'] = 'Asia';
            break;
        case 'oc':
        case 'oceania':
            $geo['geoplugin_continentCode'] = 'Oceania';
            break;
        case 'america':
        case 'na':
        case 'sa':
            $geo['geoplugin_continentCode'] = 'America';
            break;
        case 'africa':
        case 'af':
            $geo['geoplugin_continentCode'] = 'Africa';
            break;
        default:
            $geo['geoplugin_countryCode'] = env('XCODE_DEFAULT','it');
            $geo['geoplugin_continentCode'] = env('XREGION_DEFAULT','Europe');
    }

    return $geo;
}

function getRemoteIPAddress()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];

    } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1') {
        return '8.8.8.8';
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}
