<?php
namespace Tests;

use App\Infrastructure\Utils\Properties;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use App\Infrastructure\Rest\Response;
    /*
     public function testGetIndex()
    {
        $app = $this->getAppInstance();

        $container = $app->getContainer();

        // API
        $request = $this->createRequest('GET', '/mobilecmsapi/v1/cmsapi/index/calendar');
        $response = $app->handle($request);
        $payloadObject = $response->getBody();
        $payload = (string) $response->getBody();

        // Assert
        $index_data = $this->getPublicFile('/calendar/index/index.json');

        $expectedPayload = new ActionPayload(200, $index_data);
        $serializedPayload = json_encode($expectedPayload, JSON_PRETTY_PRINT);


        $this->assertResponse($expectedPayload, $response);
    }

    */

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