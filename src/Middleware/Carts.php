<?php

namespace Stentle\Webcore\Middleware;

use Stentle\Webcore\Models\Cart;
use Closure;
use Illuminate\Support\Facades\Session;

/**
 * Questo middleware si occupa di gestire lo status dei carrelli in base al paese attivo.
 *
 * Attenzione: questo middlware deve essere posto dopo il middlware localization (TODO: rendere i middlware indipendenti)
 * Class Carts
 * @package App\Http\Middleware
 */
class Carts
{

    public function __construct()
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
         $country_active=$_COOKIE['country_active'];

        //check cart_id active
        $carts = Session::get('carts');
        if (!empty($carts) && is_array($carts) && $country_active!=null && isset($carts[$country_active])) {
           //store cart_id active in session
            setcookie('cart_id', $carts[$country_active]['id'], time()+env('SESSION_DURATION')*60, '/');
            $_COOKIE['cart_id'] = $carts[$country_active]['id'];

            //updated object cart active
            $cart = new Cart();
            $cart = $cart->find($carts[$country_active]['id']);

            if ($cart != false) {
                $carts[$country_active]=$cart->getInfo();
                //check status cart and delete from session if cart active is purchased
                if ((strtoupper($cart->getInfo()['status']) == 'CART_PURCHASED') ||(strtoupper($cart->getInfo()['status']) == 'CART_PAYING'))  {
                    unset($carts[$country_active]);
                    $this->resetCookiesCart();
                }
                //update carts in session
                Session::set('carts',$carts);
            }
        } else {
            $this->resetCookiesCart();
        }

        return $next($request);
    }

    private function resetCookiesCart()
    {
        setcookie('cart_id', null, time()-3600, '/');
        $_COOKIE['cart_id'] = null;
    }

}
