<?php namespace App\Infrastructure\Services;

use App\Infrastructure\Utils\Properties;
use App\Infrastructure\Rest\Response;

abstract class AbstractService
{

    /**
     * Main directory (eg: /opt/foobar/data ).
     */
    protected $databasedir;


    protected function getConf(string $type): Properties
    {
        $conf = new Properties();
        if (\file_exists($this->getConfFileName($type))) {
            $conf->loadConf($this->getConfFileName($type));
        }
        
        return $conf;
    }

    protected function getConfFileName(string $type) : string
    {
        $this->checkType($type);

        return $this->databasedir . '/' . $type . '/index/conf.json';
    }

    protected function checkType(string $type)
    {
        if (empty($type)) {
            throw new \Exception('empty type');
        }
    }


    protected function checkParams(string $type, string $id)
    {
        $this->checkType($type);


        if (empty($id)) {
            throw new \Exception('empty id');
        }
    }


    /**
     * Initialize a default Response object.
     *
     * @return Response object
     */
    protected function getDefaultResponse() : Response
    {
        $response = new Response();
        $response->setCode(400);
        $response->setResult(new \stdClass);

        return $response;
    }
}
