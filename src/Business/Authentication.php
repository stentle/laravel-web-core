<?php

/**
 * Created by PhpStorm.
 * User: giuseppetoto
 * Date: 07/07/15
 * Time: 11:22
 */

namespace Stentle\LaravelWebcore\Business;

use Illuminate\Http\Request;
use \Stentle\LaravelWebcore\Contracts\Authentication as AuthenticationContract;
use Stentle\LaravelWebcore\Facades\ClientHttp;
use Stentle\LaravelWebcore\Models\User;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Session\Store as SessionStore;

class Authentication implements AuthenticationContract
{


    private $user;
    private $session;

    /**
     * @param User $user
     */
    public function __construct(User $user, SessionStore $session)
    {

        $this->session = $session;
        $this->user = $user;
    }

    /**
     * crea l'utente
     * @param array $data
     * @return User|bool
     */
    public function create(array $data)
    {
        $this->user->setInfo($data);
        $result = $this->user->save();
        if ($result === true)
            return $this->user;
        else
            return $result;
    }

    public function check()
    {
        $user = $this->session->get('user');
        if ($user != null && isset($user['id']))
            return true;
        else
            return false;
    }


    /**
     * @param string $username
     * @param string $password
     * @return bool|User
     */
    public function login($username, $password)
    {

        try {
            $response = ClientHttp::post('login?username=' . $username . '&password=' . $password . '&remember-me=true');
        } catch (BadResponseException $e) {

            //TODO: gestire eccezione
            var_dump($e->getRequest());
            var_dump($e->getResponse());
            return false;
        }

        $user = $this->createAccess($response);
        if ($user !== false) {
            setcookie('email', $username, time() + env('SESSION_DURATION') * 60, '/');
            setcookie('password', $password, time() + env('SESSION_DURATION') * 60, '/');
            $_COOKIE['email'] = $username;
            $_COOKIE['password'] = $password;
            return $user;
        } else {
            return false;
        }
    }


    /**
     * @param Response $response
     * @return User|bool
     * @throws \Exception
     */
    private function createAccess(Response $response)
    {
        //l'api di autentificazione restituisce un cookie che devo settare per le prossime chiamate
        /* $cookie = $response->getHeader('Set-Cookie');
         if (count($cookie) > 0) {
             $this->saveTokenSession($cookie[0]);
         }*/
        $json = json_decode($response->getBody()->getContents(), true);
        if (isset($json['data']['userId'])) {
            $user = new User();
            $me = $user->find($json['data']['userId']);
            if ($me !== false) {
                Session::put('user', $me->getInfo());
                return $me;
            } else
                return false;
        } else {
            return false;
        }
    }

    /**
     * @return mixed
     */
    public function logout()
    {
        // TODO: Implement logout() method.
    }

    /**
     * @param $request Request
     * @param $provider string
     * @param $token string
     * @return User| bool | Response
     */
    public function loginThirdParty($request, $provider, $token = null)
    {
        $data = [];
        switch ($provider) {
            case 'facebook':
                if ($token != null) {
                    $data['token'] = $token;
                } else {
                    try {
                        $user = Socialite::driver('facebook')->user();
                    } catch (ClientException $e) {
                        //TODO: gestire eccezione (token scaduto o altro)
                        return false;
                    }
                    $data['token'] = $user->token;
                }
                $data['authorityName'] = 'facebook';
                if ($request->input('email') !== null) {
                    $data['email'] = $request->input('email');
                }
                break;
            case 'google':
                if ($token != null) {
                    $data['token'] = $token;
                } else {
                    try {
                        $user = Socialite::driver('google')->user();
                    } catch (ClientException $e) {
                        //TODO: gestire eccezione (token scaduto o altro)
                        return false;
                    }
                    $data['token'] = $user->token;
                }
                $data['authorityName'] = 'google';
                if ($request->input('email') !== null) {
                    $data['email'] = $request->input('email');
                }
                break;
        }
        try {
            $response = ClientHttp::post('login/social', [
                'json' => $data,
                'headers' => ['Accept' => 'application/stentle.api-v0.1+json']
            ]);
        } catch (BadResponseException $e) {
            return false;
        }

        if ($response instanceof Response) {
            if ($response->getStatusCode() == 400) {
                return $response;
            } else {
                return $this->createAccess($response);
            }
        }
    }


    /**
     * @param $provider
     * @return mixed
     */
    public function getAuthorizationFirst($provider)
    {
        // TODO: Implement getAuthorizationFirst() method.
    }

    /**
     * @param $provider
     * @return mixed
     */
    public function getSocialUser($provider)
    {
        // TODO: Implement getSocialUser() method.
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
     * @param $user_id
     * @return mixed
     */
    public function resendConfirmationEmail($user_id)
    {
        // TODO: Implement resendConfirmationEmail() method.
    }

    /**
     * Richiesta di cambio password
     * @param $email
     * @return mixed
     */
    public function requestChangePassword($email)
    {
        $options = [];
        $url = env('PROTOCOL', 'http') . '://' . env('SITE') . '/account/recovery/token/${TOKEN}';
        $options['json'] = array('resetUrl' => $url);
        $response = ClientHttp::post('tokens/reset-password?email=' . $email, $options);
        if (substr($response->getStatusCode(), 0, 1) == '2') {
            return true;
        } else {
            return false;
        }
    }


    /** Cambio password tramite token
     * @param $token
     * @param $password
     * @return mixed
     */
    public function changePassword($token, $password)
    {
        $options = [];
        $options['json'] = array('password' => $password);
        $response = ClientHttp::patch('customers/auth?token=' . $token, $options);
        if (substr($response->getStatusCode(), 0, 1) == '2') {
            return true;
        } else {
            return false;
        }
    }

    public function clearAuthSession()
    {
        $this->session->forget('user');
        $this->session->forget('carts');
        $this->session->forget('cookie');
        $this->session->forget('cookie_ss');

        $domain = env('STENTLE_COOKIE_DOMAIN', "");
            
        setcookie("token", -1, time() - env('SESSION_DURATION') * 60, '/');
        setcookie("token_ss", -1, time() - env('SESSION_DURATION') * 60, '/');
        setcookie("stentle", -1, time() - env('SESSION_DURATION') * 60, '/', $domain);
        setcookie("stentle-ss", -1, time() - env('SESSION_DURATION') * 60, '/', $domain);
        setcookie("email", -1, time() - env('SESSION_DURATION') * 60, '/');
        setcookie("password", -1, time() - env('SESSION_DURATION') * 60, '/');
        setcookie("cart_id", -1, time() - env('SESSION_DURATION') * 60, '/');
        unset($_COOKIE['token']); 
        unset($_COOKIE['token_ss']);
        unset($_COOKIE['stentle']);
        unset($_COOKIE['stentle-ss']);
        unset($_COOKIE['email']);
        unset($_COOKIE['password']);
        unset($_COOKIE['cart_id']);
    }
}
