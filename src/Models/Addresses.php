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

class Addresses extends RestModel
{

    protected $guarded  =array('creationDate','updateDate');
    public $co;
    public $user_id;
    public $passportId;
    public $creationDate;
    public $updateDate;
    public $city;
    public $country;
    public $state;
    public $streetAddress;
    public $streetNumber;
    public $company;
    public $postalCode;
    public $description;
    public $telephone;
    public $givenName;
    public $familyName;
}