<?php

namespace Stentle\LaravelWebcore\Business;
use DrewM\MailChimp\MailChimp;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class Localization
{


    static public function getCountryRegionActive()
    {
        if (isset($_COOKIE['X-Country-Code'])) {
           return $_COOKIE['X-Country-Code'];
        } else if (isset($_COOKIE['X-Region'])) {
           return $_COOKIE['X-Region'];
        } else
            return 'it';
    }

}