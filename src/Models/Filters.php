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
 * Class Filters
 * @package Stentle\Webcore\Models
 */
class Filters extends RestModel
{
    public $resource = 'catalog/filters';
    public $headers=['Accept'=>'application/stentle.api-v0.2+json'];
    public $basic;
    public $advanced;
    public $ordering;

}