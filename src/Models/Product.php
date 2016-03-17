<?php
/**
 * Created by PhpStorm.
 * User: giuseppetoto
 * Date: 10/07/15
 * Time: 10:56
 */

namespace Stentle\Webcore\Models;

use Stentle\Webcore\Http\RestModel;

/**
 * Class Product
 * @package Stentle\Webcore\Models
 */
class Product extends RestModel
{

    public $resource = 'products';

    public $name;
    public $description;
    public $photoGallery;
    public $prices;
    public $macroCategory;
    public $microCategory;
    public $brand;
    public $availabilityTotal;
    public $story;
    public $note;
    public $attributes;
    public $saved;
    public $loved;
    public $status;
    public $published;
    public $numberOfLovers;
    public $numberOfComments;
    public $numberOfProductSharing;
    public $numberOfProductSaving;
    public $purchasable;
    public $addedToCart;
    public $requestedQuantity;
    public $attributeGroups;
}