<?php
/**
 * Created by PhpStorm.
 * User: giuseppetoto
 * Date: 16/07/15
 * Time: 18:27
 */

namespace Stentle\LaravelWebcore\Abstracts;


use Stentle\LaravelWebcore\Contracts\Operation;

abstract class Proxy implements  Operation
{
    /**
     * @var string rappresenta l'url su quale verranno fatte tutte le richieste
     */
    public  $baseUrl;
    /**
     * @var string rappresenta il nome della risorsa
     */
    public  $resource;
    /**
     * @var string The name of the field treated as this Model's unique id.
     */
    public  $idProperty='id';
    /**
     * @var string The name of the property which contains the Array of row objects. For JSON reader it's dot-separated list of property names.
     */
    public  $rootProperty;

    /**
     * @var array headers personalizzati da poter essere inseriti nella request
     */
    public $headers;

}