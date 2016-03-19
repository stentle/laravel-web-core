<?php
/**
 * Created by PhpStorm.
 * User: giuseppetoto
 * Date: 10/07/15
 * Time: 10:56
 */

namespace Stentle\LaravelWebcore\Models;
use Stentle\LaravelWebcore\Models\Product;


/**
 * Class ProductFeed
 * @package Stentle\LaravelWebcore\Models
 */
class ProductFeed extends Product
{

    public $resource = 'products_catalog';

    public $variantsGroup;


}