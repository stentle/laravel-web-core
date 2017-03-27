<?php

namespace Stentle\LaravelWebcore\Middleware;

use Stentle\LaravelWebcore\Models\Cart;
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

        $cart_session = Cart::getCartFromSession();
        if (!empty($cart_session)) {
            //updated object cart active
            $cart = new Cart();
            $cart = $cart->find($cart_session['id']);
            if ($cart != false) {
                //check status cart and delete from session if cart active is purchased
                if ((strtoupper($cart->getInfo()['status']) != 'CART_CREATED')) {
                    Cart::deleteCartFromSession($cart->id);
                } else {
                    Cart::storeCartInSession($cart);
                }
            }
        }

        return $next($request);
    }


}