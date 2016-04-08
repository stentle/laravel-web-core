<?php
/**
 * Created by PhpStorm.
 * User: giuseppetoto
 * Date: 16/07/15
 * Time: 19:04
 */

namespace Stentle\LaravelWebcore\Http;


use Stentle\LaravelWebcore\Abstracts\Entity;
use Stentle\LaravelWebcore\Contracts\DAOInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Config;

class RestModel extends Entity implements DAOInterface
{
    /**
     * @var RestProxy
     */
    protected $proxy;

    protected $mockup;

    /**
     * @var array headers personalizzati da inviare alla chiamata
     */
    protected $headers;
    public $id;
    /**
     * @var string rappresenta l'url su quale verranno fatte tutte le richieste
     */
    protected $baseUrl;
    /**
     * @var string rappresenta il nome della risorsa
     */
    protected $resource;
    /**
     * @var string The name of the field treated as this Model's unique id.
     */
    protected $idProperty = 'id';
    /**
     * @var string The name of the property which contains the Array of row objects. For JSON reader it's dot-separated list of property names.
     */
    protected $rootProperty = 'data';
    /**
     * @var string E' possibile definire un rootproperty diversa per la find.
     */
    protected $rootPropertyForMethodFind = 'data';

    public function __construct($mockup = null)
    {
        $this->addFieldsIgnore(array('baseUrl', 'resource', 'idProperty', 'rootProperty', 'proxy', 'headers'));
        $this->proxy = new RestProxy(Config::get('stentle.api'), $this->getUrl(), 'data', $this->headers);
        $this->mockup = $mockup;
    }

    public function getUrl()
    {
        if ($this->baseUrl != null)
            if ($this->id != null)
                return $this->baseUrl . '/' . $this->resource . '/' . $this->id;
            else
                return $this->baseUrl . '/' . $this->resource;
        else
            return $this->resource;
    }

    public function all()
    {
        $instance = new static;
        $proxy = $instance->proxy;

        if ($proxy instanceof RestProxy) {


            $proxy->resource = $this->getUrl(); //fix:la risorsa potrebbe essere cambiata anche dopo che l'oggetto è stato istanziato
            try {
                if ($this->mockup == null) {
                    $response = $proxy->read();
                    $json = json_decode($response->getBody()->getContents(), true);
                } else
                    $json = json_decode($this->mockup, true);
                $data = $this->getValueFromJsonArray($instance->rootProperty, $json);
                if ($data == null)
                    return array();
                $items = array();
                foreach ($data as $item) {
                    $instance = new static;
                    $instance->setInfo($item, $isGuard = false);
                    $instance->baseUrl = $this->baseUrl;
                    $instance->resource = $this->resource;
                    $items[] = $instance;
                }
                return $items;
            } catch (BadResponseException $e) {
                //TODO: gestire eccezioni 500/404/401;
                var_dump($e->getRequest());
                var_dump($e->getResponse());
                throw new \Exception();
                return false;
            }
        }
    }

    public function paginate($perPage = 15)
    {
        // TODO: Implement paginate() method.
    }

    /**
     * @param bool|true $force se true forza la post nononstante sia definito l'id
     * @return bool
     */
    public function save($force = false)
    {
        $idProp = $this->idProperty;
        try {
            $this->proxy->resource = $this->getUrl();  //fix:la risorsa potrebbe essere cambiata anche dopo che l'oggetto è stato istanziato

            if (empty($this->$idProp) || $force) {
                $r = $this->proxy->create($this->getInfo());
            } else {
                $r = $this->proxy->update($this->getInfo(array('id')), $this->$idProp);
            }
            $statusCode = $r->getStatusCode();
            if ($statusCode == 200 || $statusCode == 201) {
                $json = $r->getBody()->getContents();
                $json = json_decode($json, true);
                $this->setInfo($json[$this->rootPropertyForMethodFind], $isGuard = false);
                return true;
            } else {
                $json = $r->getBody()->getContents();
                return $json;
            }
        } catch (ClientException $e) {
            return false;
        }
    }

    public function update(array $data, $id)
    {
        // TODO: Implement update() method.
    }

    public function delete()
    {
        $idProp = $this->idProperty;
        try {
            $this->proxy->resource = $this->getUrl();  //fix:la risorsa potrebbe essere cambiata anche dopo che l'oggetto è stato istanziato

            $r = $this->proxy->destroy($this->$idProp);

            $statusCode = $r->getStatusCode();
            if ($statusCode == 200 || $statusCode == 201) {
                $json = $r->getBody()->getContents();
                $json = json_decode($json, true);
                return true;
            } else {
                $json = $r->getBody()->getContents();
                return $json;
            }
        } catch (ClientException $e) {
            return false;
        }
    }

    /**
     * @param $id
     * @return static
     */
    public function find($id)
    {
        $instance = new static;
        $instance->baseUrl = $this->baseUrl;
        $instance->resource = $this->resource;

        $instance->proxy->resource = $this->getUrl();  //fix:la risorsa potrebbe essere cambiata anche dopo che l'oggetto è stato istanziato
        $proxy = $instance->proxy;
        if ($instance->rootPropertyForMethodFind != NULL)
            $root = $instance->rootPropertyForMethodFind;
        else
            $root = $instance->rootProperty;
        if ($proxy instanceof RestProxy) {
            try {
                if ($this->mockup == null) {
                    if ($this->id == null)
                        $response = $proxy->read($id);
                    else
                        $response = $proxy->read(); //significa che l'id è già all'interno dell'url della risorsa.
                    $json = json_decode($response->getBody()->getContents(), true);
                } else {
                    $json = json_decode($this->mockup, true);
                }
                $data = $instance->getValueFromJsonArray($root, $json);

                if ($data == null)
                    return false;
                $instance->setInfo($data, $idGuard = false);
                return $instance;
            } catch (BadResponseException $e) {
                return false;
            }
        }
    }


    public function findBy(array $data)
    {
        // TODO: Implement findBy() method.
    }


    /**
     * @param $className nome della classe da istanziare per l'associazione
     * @param null $namespace namespace della classe (facoltativo)
     * @param null $path_resource possibilità di modificare il resource
     * @return array|bool
     * @throws \Exception
     */
    public function hasMany($className, $namespace = null, $path_resource = null)
    {
        if ($namespace != null) {
            $class = "$namespace\\$className";
        } else {
            $class = "\Stentle\LaravelWebcore\Models\\$className";
        }
        $model = new $class;

        if ($model instanceof RestModel) {
            if (empty($this->id))
                throw new \Exception("ID empty");

            $model->mockup = $this->mockup;
            $model->resource = $this->resource . '/' . $this->id . '/' . $model->resource;
            return $model->all();
        } else {
            return array();
        }

    }

    /**
     * Permette di specificare un'associazione con altre entità
     * @param $path_entity namespace dell'entity da istanziare
     * @param $path_resource path da utilizzare per effettuare l'associazione
     * @return mixed restituisce un'istanza di $namespace
     */
    public function hasOne($namespace, $path_resource)
    {
        // TODO: Implement hasOne() method.
    }

    protected function findSubresource($namespace, $id)
    {

        $class = "\Stentle\LaravelWebcore\Models\\$namespace";
        $model = new $class;
        if ($model instanceof RestModel) {
            if (empty($this->id))
                throw new \Exception("ID empty");

            $model->resource = $this->resource . '/' . $this->id . '/' . $model->resource;

            return $model->find($id);
        } else {
            throw new \Exception("$namespace model not exist");
        }

    }

    protected function deleteSubresource($namespace, $id)
    {

        $class = "\Stentle\LaravelWebcore\Models\\$namespace";
        $model = new $class;
        if ($model instanceof RestModel) {
            if (empty($this->id))
                throw new \Exception("ID empty");

            $model->resource = $this->resource . '/' . $this->id . '/' . $model->resource;
            $model->id = $id;
            return $model->delete();
        } else {
            throw new \Exception("$namespace model not exist");
        }

    }


    /**
     * crea l'associazione per una sottorisorsa
     * @param RestModel $model
     * @param bool $forceSaveWithPost se true ,anche se l'id è specificato forzo il save a diventare una POST (invece di una PUT)
     * @return mixed
     */
    public function add(RestModel $model, $forceSaveWithPost = false)
    {
        if (empty($this->id))
            throw new \Exception("ID empty");

        $model->resource = $this->resource . '/' . $this->id . '/' . $model->resource;
        return $model->save($forceSaveWithPost);
    }

    /**
     * Consente di poter navigare in un array passando una chiave in formato json
     * @param $jsonkey Esempio: data.items
     * @param $array l'array sul quale navigare
     * @return array l'array che contiene il valore che si voleva raggiungere
     */
    private function getValueFromJsonArray($jsonkey, $array)
    {
        if ($jsonkey != null) {
            $keys = explode('.', $jsonkey);

            for ($i = 0; $i < count($keys); $i++) {
                if (isset($array[$keys[$i]])) {
                    $array = $array[$keys[$i]];
                } else
                    return null;
            }
        }
        return $array;
    }
}