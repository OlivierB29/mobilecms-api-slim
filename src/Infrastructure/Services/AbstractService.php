<?php

namespace App\Infrastructure\Services;

use App\Infrastructure\Rest\Response;
use App\Infrastructure\Utils\Properties;

abstract class AbstractService
{
    /**
     * Main directory (eg: /opt/foobar/data ).
     */
    protected $databasedir;

    protected $recordConf;

    protected function getRecordConf(string $type): Properties
    {
        if (empty($this->recordConf)) {
            $this->recordConf = new Properties();

            $this->recordConf->loadRecordConf($this->getDatabaseDir(), $this->getConfFileName($type));
        }

        return $this->recordConf;
    }

    protected function getDatabaseDir(): string
    {
        if (empty($this->databasedir)) {
            throw new \Exception('databasedir is  empty');
        }

        if (empty(\realpath($this->databasedir))) {
            throw new \Exception('databasedir : invalid path');
        }

        return $this->databasedir;
    }

    protected function getConfFileName(string $type): string
    {
        $this->checkType($type);

        return \realpath($this->getDatabaseDir().'/'.$type.'/index/conf.json');
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
    protected function getDefaultResponse(): Response
    {
        $response = new Response();
        $response->setCode(400);
        $response->setResult(new \stdClass());

        return $response;
    }
}
