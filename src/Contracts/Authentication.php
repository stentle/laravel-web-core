<?php
namespace Stentle\Webcore\Contracts;
use Stentle\Webcore\Models\User;

/**
 * Interface Authentication
 * @package Stentle\Webcore\Contracts
 */
interface Authentication {
    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data);
    /**
     * @param $request
     * @return User|bool
     */
    public function login($username,$password);
    /**
     * @return mixed
     */
    public function logout();
    /**
     * @param $request
     * @param $provider
     * @return mixed
     */

    /**
     * Determina se l'utente corrente è loggato
     * @return bool
     */
    public function check();


    /**si occupa di effettuare il login tramite tramite social
     * @param $request
     * @param $provider
     * @return bool|User
     */
    public function loginThirdParty($request, $provider);
    /**
     * @param $provider
     * @return mixed
     */
    public function getAuthorizationFirst($provider);
    /**
     * @param $provider
     * @return mixed
     */
    public function getSocialUser($provider);
    /**
     * @param $token
     * @return mixed
     */
    public function confirmAccount($token);
    /**
     * @param $user_id
     * @return mixed
     */
    public function resendConfirmationEmail($user_id);


} 