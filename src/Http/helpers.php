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
    if (isset($_SERVER['GEOIP_CITY_COUNTRY_CODE'])) {
        $geo['geoplugin_countryCode'] = strtolower($_SERVER['GEOIP_CITY_COUNTRY_CODE']);
        $geo['geoplugin_continentCode'] = strtolower($_SERVER['GEOIP_CITY_CONTINENT_CODE']);
        return $geo;
    } else {
        $geo = unserialize(file_get_contents("http://www.geoplugin.net/php.gp?ip=$ip"));
        $geo['geoplugin_continentCode']='eu';
    }

    return $geo;
}

function getRemoteIPAddress()
{
    return $_SERVER['REMOTE_ADDR'];
}
