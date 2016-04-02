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
 * Class User
 * @package App\StentleCore\Models
 * @property string|array $country
 */
class ShippingAddress extends Addresses
{
    protected $resource = 'shipping-addresses';
    protected  $rootProperty='data.items';
    protected  $rootPropertyForMethodFind='data';
    protected $guarded = array('creationDate', 'updateDate');
    public $defaultShippingAddress;
}