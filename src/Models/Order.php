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
class Order extends RestModel
{
    protected $resource = 'purchase-orders';
    protected  $rootProperty='data.items';
    protected  $rootPropertyForMethodFind='data';
    public $productOrderList;
    public $orderTitle;
    public $status;
    public $identifier;
    public $placedDate;
    public $totals;
    public $customerShippingAddress;
    public $customerBillingAddress;
    public $shippingService;
    public $currency;
}