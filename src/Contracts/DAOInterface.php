<?php
namespace Stentle\LaravelWebcore\Contracts;

use Stentle\LaravelWebcore\Http\RestModel;

interface DAOInterface
{
    public function all();

    public function paginate($perPage = 15);

    public function save($force);

    public function delete();

    public  function find($id);
    public function findBy(array $data);

    /**
     * Permette di specificare un'associazione con altre entità
     * @param $path_entity namespace dell'entity da istanziare
     * @param $path_resource path da utilizzare per effettuare l'associazione
     * @return array restituisce un array di istanze di $namespace
     */
    public function hasMany($namespace, $path_resource);
    /**
     * Permette di specificare un'associazione con altre entità
     * @param $path_entity namespace dell'entity da istanziare
     * @param $path_resource path da utilizzare per effettuare l'associazione
     * @return mixed restituisce un'istanza di $namespace
     */
    public function hasOne($namespace, $path_resource);

    /**
     * crea l'associazione per una sottorisorsa
     * @param RestModel $model
     * @param bool $forceSaveWithPost se true ,anche se l'id è specificato forzo il save a diventare una POST (invece di una PUT)
     * @return mixed
     */
    public function add(RestModel $model,$forceSaveWithPost=false);
} 