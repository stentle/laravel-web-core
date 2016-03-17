<?php
/**
 * Created by PhpStorm.
 * User: giuseppetoto
 * Date: 16/09/15
 * Time: 16:58
 */


function isActiveLanguage($value)
{
    if (isset($_COOKIE['locale']) && $_COOKIE['locale'] == $value)
        return 'active';
}

function isItemMenuActive($value){

    if(strpos($_SERVER['REQUEST_URI'],$value)!==false){
        return 'active';
    }
}


/**
 * Recupera le informazioni di geolocalizzazione tramite ip
 * EXAMPLE:
 *  $city = $geo["geoplugin_city"];
    $region = $geo["geoplugin_regionName"];
    $country = $geo["geoplugin_countryName"];
 * @return mixed
 */
function getGeoByIp($ip){
    if(isset($_SERVER['GEOIP_CITY_COUNTRY_CODE'])){
        $geo['geoplugin_countryCode']=strtolower($_SERVER['GEOIP_CITY_COUNTRY_CODE']);
    }else{
        $geo = unserialize(file_get_contents("http://www.geoplugin.net/php.gp?ip=$ip"));
    }

    return $geo;
}

function getRemoteIPAddress() {
    return $_SERVER['REMOTE_ADDR'];
}