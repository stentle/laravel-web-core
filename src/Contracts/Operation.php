<?php


namespace Stentle\LaravelWebcore\Contracts;

/**
 * Rappresenta le azioni che possono essere effettuata su una risorsa
 * @package Stentle\LaravelWebcore\Contracts
 */
interface Operation
{
    public function create(array $data);
    public function update(array $data,$id);
    public function read($id=null);
    public function destroy($id);
}