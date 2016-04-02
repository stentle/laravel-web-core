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

class User extends RestModel
{

    protected $resource='customers';

    protected $fillable  = array('givenName','gender','company','profession','primaryEmail','password','familyName','country','nickname','taxid','birthDate','telephone');

    public $givenName;
    public $familyName;
    public $birthDate;

    public $taxid;
    public $telephone;
    public $primaryEmail;
    public $password;
    public $company;

    public $profession;

    public $creationDate;
    public $updateDate;
    public $gender;
    public $nickname;
    public $securityRole;
    public $loginCounter;
    public $numberOfComments;
    public $numberOfBadges;
    public $numberOfProductsPublished;
    public $numberOfProductsSaved;
    public $numberOfProductsLoved;
    public $numberOfProductsShared;
    public $numberOfPurchaseOrders;

    protected $country;



    public function orders(){
        return $this->hasMany('Order');
    }



    public function AddShippingAddress(ShippingAddress $model){
        $model->resource = $this->resource.'/'.$this->id.'/'.$model->resource;
        $model->save();
    }
    public function shippingAddresses(){
        return $this->hasMany('ShippingAddress');
    }

    /***
     * @param $id
     * @return ShippingAddress
     * @throws \Exception
     */
    public function findShippingAddress($id){
        return $this->findSubresource('ShippingAddress',$id);
    }
    /***
     * @param $id
     * @return BillingAddress
     * @throws \Exception
     */
    public function findBillingAddress($id){
        return $this->findSubresource('BillingAddress',$id);
    }

    public function deleteShippingAddress($id){
        return $this->deleteSubresource('ShippingAddress',$id);
    }

    public function deleteBillingAddress($id){
        return $this->deleteSubresource('BillingAddress',$id);
    }
    public function billingAddresses(){
        return $this->hasMany('BillingAddress');
    }

    /**
     * la variabilità country è in realtà una sotto risorsa. Pertanto va trattata
     * @param $name
     */
    public function setCountry($name){
        if(is_string($name)){
            $this->country=array('name'=>$name);
        }else{
            $this->country=$name;
        }
    }

    public function getCountry(){
        return $this->country;
    }


    /**
     * @param $data
     * @return mixed
     */
    public function findByUserNameOrCreate($data, $provider)
    {
        // TODO: Implement findByUserNameOrCreate() method.
    }

    /**
     * @param $provider
     * @param $providerData
     * @param $user
     * @return mixed
     */
    public function checkIfUserNeedsUpdating($provider, $providerData, $user)
    {
        // TODO: Implement checkIfUserNeedsUpdating() method.
    }

    /**
     * @param $id
     * @param $input
     * @return mixed
     */
    public function updateProfile($id, $input)
    {
        // TODO: Implement updateProfile() method.
    }

    /**
     * @param $input
     * @return mixed
     */
    public function changePassword($input)
    {
        // TODO: Implement changePassword() method.
    }

    /**
     * @param $token
     * @return mixed
     */
    public function confirmAccount($token)
    {
        // TODO: Implement confirmAccount() method.
    }

    /**
     * @param $user
     * @return mixed
     */
    public function sendConfirmationEmail($user)
    {
        // TODO: Implement sendConfirmationEmail() method.
    }
}