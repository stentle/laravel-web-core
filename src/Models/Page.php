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
 * Class User
 * @package Stentle\Webcore\Models
 * @property string|array $country
 */

class Page extends RestModel
{

    protected $resource='cms/frontend/cmspages';

    protected $fillable  = array('slides','groups');

    public $slides;
    public $groups;

}