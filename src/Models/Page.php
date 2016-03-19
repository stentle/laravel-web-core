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
 * @package Stentle\LaravelWebcore\Models
 * @property string|array $country
 */

class Page extends RestModel
{

    protected $resource='cms/frontend/cmspages';

    protected $fillable  = array('slides','groups');

    public $slides;
    public $groups;

}