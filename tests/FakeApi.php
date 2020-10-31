<?php
namespace Tests;

use App\Infrastructure\Utils\Properties;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use App\Infrastructure\Rest\Response;

final class FakeApi
{

        /**
    * get JSON conf
    * @return \stdClass JSON conf
    */
    public function getConf()
    {
        return Properties::getInstance()->getConf();
    }

    /**
     * Get main working directory.
     *
     * @return string rootDir main working directory
     */
    public function getRootDir(): string
    {
        return Properties::getInstance()->getRootDir();
    }

    /**
     * Get public directory.
     *
     * @return string publicdir main public directory
     */
    public function getPublicDirPath(): string
    {
        return $this->getRootDir() . $this->getConf()->{'publicdir'};
    }

    /**
     * Get public directory.
     *
     * @return string publicdir main public directory
     */
    public function getMediaDirPath(): string
    {
        return $this->getRootDir() . $this->getConf()->{'media'};
    }
}
