<?php
namespace Stentle\LaravelWebcore\Contracts;

use Illuminate\Http\Request;
use Stentle\LaravelWebcore\Models\User;

/**
 * Interface Authentication
 * @package Stentle\LaravelWebcore\Contracts
 */
interface Authentication
{
    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data);

    /**
     * @param $username string
     * @param $password string
     * @return mixed
     */
    public function login($username, $password);

    /**
     * @return mixed
     */
    public function logout();

    /**
     * Determina se l'utente corrente è loggato
     * @return bool
     */
    public function check();


    /**si occupa di effettuare il login tramite tramite social
     * @param $request Request
     * @param $provider string
     * @param $token string facoltativo. Se specificato non utilizza socialiate
     * @return bool|User
     */
    public function loginThirdParty($request, $provider,$token=null);

    /**
     * @param $provider
     * @return mixed
     */
    public function getAuthorizationFirst($provider);

    /**
     * @param $provider string
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