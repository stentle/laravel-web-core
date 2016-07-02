<?php
/**
 * Created by PhpStorm.
 * User: giuseppetoto
 * Date: 10/07/15
 * Time: 10:56
 */

namespace Stentle\LaravelWebcore\Models;
use Illuminate\Support\Facades\Session;
use Stentle\LaravelWebcore\Business\Localization;
use Stentle\LaravelWebcore\Facades\ClientHttp;
use Stentle\LaravelWebcore\Http\RestModel;

class Cart extends RestModel
{

    protected $resource='carts';
    public $productCartList;
    public $itemQuantity;
    public $status;
    public $currency;
    public $currencySymbol;
    public $totals;
    public $country;
    public $message;

    public $settings;


    public static function getCartFromSession()
    {
        $country_active = Localization::getCountryRegionActive();
        $carts = Session::get('carts');
        if (is_array($carts) && isset($carts[$country_active])) {
            return $carts[$country_active];
        }

        return null;
    }

    private static function storeCartInSession($cart)
    {
        $country_active = Localization::getCountryRegionActive();
        $carts = Session::get('carts');
        if (!is_array($carts))
            $carts = [];
        $carts[$country_active] = $cart->getInfo();
        Session::put('carts', $carts);
        Cart::activeCartInSession($cart);
    }

    private static function activeCartInSession($cart)
    {
        setcookie('cart_id', $cart->id, time() + env('SESSION_DURATION') * 60, '/');
        $_COOKIE['cart_id'] = $cart->id;
    }

    /**
     * Si occupa di creare il deal usando l'api cart  di stentle
     * @param $product_id id prodotto
     * @param $duration duranta espressa in minuti dell'offerta
     * @param $price prezzo
     * @param $quantity quantità dei prodotti richiesti
     * @return Cart|bool
     */

    public static function create($product_id, $duration, $price, $quantity = 1)
    {

        $cart = new Cart();

        $cart->productCartList = array();
        $cart->productCartList[] = array('id' => $product_id, 'requestedQuantity' => $quantity);

        $cart->settings = array('deal-duration' => $duration, 'deal-price' => $price);
        if ($cart->save() && !empty($cart->id)) {
            Cart::storeCartInSession($cart);
            return $cart;
        } else {
            return false;
        }

    }

    public function checkout($config)
    {

        if ($this->id != null) {
            $options['json'] = $config;

            $response = ClientHttp::post($this->resource . '/' . $this->id . '/checkout', $options);

            if ($response->getStatusCode() == 200 || $response->getStatusCode() == 201) {
                $json= json_decode($response->getBody()->getContents(), true);
                $this->setInfo($json['data']);
                return true;
            } else {
                return false;
            }
        } else
            return false;

    }

    public function setShippingAddress()
    {

        if ($this->id != null) {

        } else {
            throw new \Exception("cart_id is notdefined");
        }
    }

    public function setBillingAddress()
    {
        if ($this->id != null) {

        } else {
            throw new \Exception("cart_id is notdefined");
        }
    }
}