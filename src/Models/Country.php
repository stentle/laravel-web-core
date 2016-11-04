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
 * Class Country
 * @package Stentle\LaravelWebcore\Models
 */
class Country extends RestModel
{
    protected $rootProperty = 'data';
    protected $resource = 'geo/countries';

    public $euroZone;
    public $isoCode;
    public $iso3Code;
    public $name;
    public $localeName;
    public $region;
    public $taxValue;
    public $allowed;
    public $printableName;
}