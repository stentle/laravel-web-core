<?php
/**
 * Created by PhpStorm.
 * User: giuseppetoto
 * Date: 10/07/15
 * Time: 10:56
 */

namespace Stentle\LaravelWebcore\Models;

use Stentle\LaravelWebcore\Http\RestModel;

class Cart extends RestModel
{

    protected $resource='carts';
    public $productCartList;
    public $itemQuantity;
    public $status;
    public $currency;
    public $currencySymbol;
    public $totals;
    public $country;
    public $message;
    public function add(Product $product){
        if(empty($this->id))
            throw new \Exception("idCart not defined");
        $product->resource=$this->resource.'/'.$this->id.'/'.$product->resource;
        return $product->save(true);
    }

}