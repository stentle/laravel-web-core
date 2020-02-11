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
class Order extends RestModel
{
    protected $resource = 'purchase-orders';
    protected  $rootProperty = 'data.items';
    protected  $rootPropertyForMethodFind = 'data';
    public $headers = ['Accept-Language' => ''];
    public $productOrderList;
    public $orderTitle;
    public $status;
    public $identifier;
    public $placedDate;
    public $sub_order_list;
    public $totals;
    public $customerShippingAddress;
    public $customerBillingAddress;
    public $shippingService;
    public $currency;
    public $travel;
}
