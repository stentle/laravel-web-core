<?php

namespace Stentle\LaravelWebcore\Facades;
use Illuminate\Support\Facades\Facade;

class ClientHttp extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'clienthttp';
    }
}

