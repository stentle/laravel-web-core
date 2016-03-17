<?php
namespace Stentle\Webcore\Http;

use Stentle\Webcore\Facades\ClientHttp;
use GuzzleHttp\Psr7\Response;

class RestProxy extends \Stentle\Webcore\Abstracts\Proxy
{
    public function __construct($baseUrl, $resource, $rootProperty, $headers = null)
    {
        // $this->client = new Client(['http_errors' => true,'cookies' => true,'base_uri' => $baseUrl,'headers'=>['Accept-Language'=>'it','Content-Type'=>'application/json','x-domain'=>'picnik_local']]);
        $this->resource = $resource;
        $this->rootProperty = $rootProperty;
        $this->headers = $headers;
    }

    public function create(array $data)
    {
        $options = [];
        $options['json'] = $data;
        if (is_array($this->headers))
            $options['headers'] = $this->headers;
        return  ClientHttp::post($this->resource, $options);
    }

    public function update(array $data, $id)
    {
        $options=[];
        $options['json'] = $data;
        if (is_array($this->headers))
            $options['headers'] = $this->headers;

        return ClientHttp::put($this->resource . '/' . $id, $options);
        /*$request = new \GuzzleHttp\Psr7\Request('PUT', $this->resource.'/'.$id,['content-type' => 'application/json'],json_encode($data));
        $response = ClientHttp::send($request);*/
    }

    /**
     * @param null $id
     * @return Response
     */
    public function read($id = null)
    {

        $resource = $this->resource;
        if (!empty($id))
            $resource = $this->resource . '/' . $id;

        $options=[];
        if (is_array($this->headers))
            $options['headers'] = $this->headers;

        $response = ClientHttp::get($resource,$options);
        return $response;
    }

    public function destroy($id)
    {
        $options=[];
        if (is_array($this->headers))
            $options['headers'] = $this->headers;

        $response = ClientHttp::delete($this->resource . "/$id",$options);
        return $response;
    }
}