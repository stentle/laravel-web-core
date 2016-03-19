<?php
/**
 * Created by PhpStorm.
 * User: giuseppetoto
 * Date: 10/07/15
 * Time: 10:56
 */

namespace Stentle\LaravelWebcore\Models;

use Stentle\LaravelWebcore\Http\RestModel;

/**
 * Class Product
 * @package Stentle\LaravelWebcore\Models
 */
class Catalog extends RestModel
{
    protected $rootProperty = 'data.items';
    protected $resource = 'catalog?limit=300&pageNumber=1';

    public $name;
    public $description;
    public $coverPhotoUrl;
    public $availabilityTotal;
    public $prices;
    public $macroProductCategory;
    public $microProductCategory;
    public $brand;
    public $sellingPrice;
    public $saved;
    public $loved;
    public $currency;
    public $numberOfLovers;
    public $numberOfComments;
    public $numberOfProductSharing;
    public $numberOfProductSaving;

    public $purchasable;
    public $addedToCart;
}