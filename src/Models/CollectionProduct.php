<?php

/**
 * Created by PhpStorm.
 * User: giuseppetoto
 * Date: 10/07/15
 * Time: 10:56
 */
//Aggiunti campi della chiamata arricchita

namespace Stentle\LaravelWebcore\Models;

class CollectionProduct extends \Stentle\LaravelWebcore\Models\ProductFeed

{
    public $resource = 'products?projection=productFeedElement';
    public $coverPhotoUrl;
    public $sellingPrice;
    public $retailPrice;
    public $currency;
    public $price;
}
