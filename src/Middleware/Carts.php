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

    /**
     * The URIs that should be excluded from rollback cart
     *
     * @var array
     */
    protected $exceptRollback = ['carts/*'];

    public function __construct()
    {
    }


    /**
     * Determine if the request has a URI that should pass through CSRF verification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function inExceptArray($request)
    {
        foreach ($this->exceptRollback as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->is($except)) {
                return true;
            }
        }

        return false;
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
                $status =strtoupper($cart->getInfo()['status']);
                if($status == Cart::CART_PAYING && env('X_DOMAIN') != 'pricebox' && env('X_DOMAIN') != 'vhernier' && env('X_DOMAIN') != 'landoffashion'){
                    if(!$this->inExceptArray($request)){
                        $cart->rollback();
                    }
                    Cart::storeCartInSession($cart);
                }else if ($status == Cart::CART_CREATED){
                    Cart::storeCartInSession($cart);
                } else {
                    //check status cart and delete from session if cart active is purchased
                    Cart::deleteCartFromSession($cart->id);
                }
            }
        }

        return $next($request);
    }


}
