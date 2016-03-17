<?php

namespace Stentle\Webcore\Facades;
use Illuminate\Support\Facades\Facade;

class ClientHttp extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'clienthttp';
    }
}

