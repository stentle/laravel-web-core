<?php

namespace Stentle\LaravelWebcore\Models;
use Stentle\LaravelWebcore\Http\RestModel;

class Collection extends RestModel
{
    protected $resource = 'collections';
    protected $rootProperty='items';
    public $bannerDesktop;
    public $bannerMobile;
    public $description;
    public $discount;
    public $name;
    public $icon;
    public $id;

    function products()
    {
        return $this->hasMany('CollectionProduct');
    }
}