<?php
/**
 * Created by PhpStorm.
 * User: giuseppetoto
 * Date: 10/07/15
 * Time: 10:57
 */

namespace Stentle\LaravelWebcore\Contracts;


interface User
{
    public function register($data);
    /**
     * @param $data
     * @return mixed
     */
    public function findByUserNameOrCreate($data, $provider);
    /**
     * @param $provider
     * @param $providerData
     * @param $user
     * @return mixed
     */
    public function checkIfUserNeedsUpdating($provider, $providerData, $user);
    /**
     * @param $id
     * @param $input
     * @return mixed
     */
    public function updateProfile($id, $input);
    /**
     * @param $input
     * @return mixed
     */
    public function changePassword($input);
    /**
     * @param $token
     * @return mixed
     */
    public function confirmAccount($token);
    /**
     * @param $user
     * @return mixed
     */
    public function sendConfirmationEmail($user);
}