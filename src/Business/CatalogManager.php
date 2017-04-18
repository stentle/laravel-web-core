<?php

namespace Stentle\LaravelWebcore\Business;

use Stentle\LaravelWebcore\Facades\ClientHttp;

class CatalogManager
{


    private static $instance = null;
    private $filter;

    private function __construct()
    {


    }

    /***
     * @return FiltersManager
     */
    public static function getInstance()
    {

        if (self::$instance == null) {
            $c = __CLASS__;
            self::$instance = new $c();

        }


        return self::$instance;
    }


    public static function createFilter($basic, $advanced, $range, $pageNumber, $limit)
    {
        if ($pageNumber <= 0)
            $pageNumber = 1;

        return ['filterAttributes' => [
            'basic' => $basic,
            'advanced' => $advanced,
            'range' => $range
        ],
            'pageNumber' => $pageNumber,
            'limit' => $limit];
    }

    public static function search($filter = null)
    {
        $options = [];

        if ($filter == null)
            $filter = self::createFilter([], [], [], 1, 100);

        $options['headers']['Accept'] = 'application/stentle.api-v0.2+json';
        $options['json'] = $filter;

        $response = ClientHttp::post('catalog', $options);

        if ($response->getStatusCode() >= 400) {
            new \Exception("catalog search request failed with code: " . $response->getStatusCode(), $response->getStatusCode());
        } else {
            $json = json_decode($response->getBody()->getContents(), true);
            return $json;
        }
    }
}