<?php

namespace Tests;

use App\ApiConstants;
use App\Application\Actions\ActionPayload;
use App\Infrastructure\Rest\Response;
use App\Infrastructure\Utils\Properties;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Headers;
use Slim\Psr7\Request;
use Slim\Psr7\Uri;
use Tuupola\Http\Factory\StreamFactory;
use Tuupola\Http\Factory\UploadedFileFactory;

// reminder : PHPUnit autoloader seems to import files with an alphabetic order.

abstract class ApiTest extends TestCase
{
    protected $path = '';
    protected $headers = [];
    protected $REQUEST = [];
    protected $SERVER = [];
    protected $GET = [];
    protected $POST = [];

    protected $requestbody = [];
    protected $memory1 = 0;
    protected $memory2 = 0;

    protected $API;

    protected $postformdata = false;

    protected function setUp(): void
    {
        Properties::init(__DIR__.'/../tests-data', __DIR__.'/conf.json');

        $this->path = '';
        $this->headers['HTTP_ACCEPT'] = 'application/json';
        $this->headers['Content-Type'] = 'application/json';
        $this->REQUEST = [];
        $this->SERVER = [];
        $this->GET = [];
        $this->POST = [];
        $this->API = new FakeApi();
    }

    protected function memory()
    {
        $this->memory1 = $this->memory2;

        $this->memory2 = memory_get_usage();

        return $this->memory2 - $this->memory1;
    }

    protected function printError(Response $response)
    {
        /*  if ($response->getCode() != 200) {
              echo 'ERROR ' . $response->getCode() . ' : ' . $response->getEncodedResult();
          }*/
    }

    protected function fileRequest($verb, $pathArg, $files): Response
    {
        $path = '';
        // ignore request parameters : TODO ignore them into Slim
        if (strpos($pathArg, '?') !== false) {
            $path = substr($pathArg, 0, strpos($pathArg, '?'));
        } else {
            $path = $pathArg;
        }

        // request with verb and path
        // $token = 'TEST';
        //  $headers = ['HTTP_ACCEPT' => 'application/json', 'Authorization' => 'Bearer ' . $token];
        if (\count($files)) {
            $cookies = [];
            $serverParams = [];
            $request = $this->createFilesRequest($verb, $path, $this->headers, $cookies, $serverParams, $files);
        } else {
            $request = $this->createRequest($verb, $path, $this->headers);
        }

        // emulate POST body

        if (isset($this->POST['requestbody'])) {
            if ($this->postformdata) {
                $contents = \json_decode($this->POST['requestbody']);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $requestbody['requestbody'] = $contents;
                    $request = $request->withParsedBody($requestbody);
                }
            } else {
                $contents = \json_decode($this->POST['requestbody']);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $request = $request->withParsedBody($contents);
                }
            }
        }
        $app = $this->getAppInstance();
        // execute
        $response = $app->handle($request);

        return $this->toOldResponse($response);
    }

    /**
     * execute request throw slim, using previous class Response.
     */
    protected function request($verb, $pathArg): Response
    {
        $path = '';
        // ignore request parameters : TODO ignore them into Slim
        if (strpos($pathArg, '?') !== false) {
            $path = substr($pathArg, 0, strpos($pathArg, '?'));
        } else {
            $path = $pathArg;
        }

        // request with verb and path
        // $token = 'TEST';
        //  $headers = ['HTTP_ACCEPT' => 'application/json', 'Authorization' => 'Bearer ' . $token];

        $request = $this->createRequest($verb, $path, $this->headers);

        if (isset($this->POST['requestbody'])) {
            if ($this->postformdata) {
                $contents = \json_decode($this->POST['requestbody']);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $requestbody['requestbody'] = $contents;
                    $request = $request->withParsedBody($requestbody);
                }
            } else {
                $contents = \json_decode($this->POST['requestbody']);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $request = $request->withParsedBody($contents);
                }
            }
        }
        $app = $this->getAppInstance();
        // execute
        $response = $app->handle($request);

        return $this->toOldResponse($response);
    }

    /**
     * convert a PSR to the previous Response class.
     */
    protected function toOldResponse(ResponseInterface $psrResponse): Response
    {
        $result = new Response();
        $result->setCode($psrResponse->getStatusCode());

        $jsonResponse = \json_decode($psrResponse->getBody()->__toString());
        //$body = \json_encode($jsonResponse->{'data'});
        if (isset($jsonResponse)) {
            if (isset($jsonResponse->{'data'})) {
                $result->setResult($jsonResponse->{'data'});
            } else {
                $result->setResult($jsonResponse);
            }
        } else {
            $result->setResult(\json_decode('{}'));
        }

        return $result;
    }

    protected function getPublicFile(string $file): string
    {
        return file_get_contents(Properties::getInstance()->getPublicDirPath().'/'.$file);
    }

    protected function assertResponse(ActionPayload $expected, ResponseInterface $actual)
    {
        if ($expected->getData() != null) {
            $jsonResponse = \json_decode((string) $actual->getBody());

            if (isset($jsonResponse->{'data'})) {
                $bodyStr = \json_encode($jsonResponse->{'data'});

                $this->assertJsonStringEqualsJsonString($expected->getData(), $bodyStr);
            }
        }

        if ($expected->getError() != null) {
            $this->assertEquals($expected->getError()->getDescription(), $actual->getReasonPhrase());
        }

        $this->assertEquals($expected->getStatusCode(), $actual->getStatusCode());
    }

    /**
     * get JSON conf.
     *
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
        return $this->getRootDir().$this->getConf()->{'publicdir'};
    }

    public function getPrivateDirPath(): string
    {
        return $this->getRootDir().$this->getConf()->{'privatedir'};
    }

    /**
     * Get public directory.
     *
     * @return string publicdir main public directory
     */
    public function getMediaDirPath(): string
    {
        return $this->getRootDir().$this->getConf()->{'media'};
    }

    // UploadedFileInterface[]

    protected function toUploadedFileInterface(array $files): array
    {
        $result = [];
        /*
                $files = [
                ['name'=>$filename,'type'=>'application/pdf','tmp_name'=> $mockUploadedFile,'error'=>0,'size'=>24612]
                ];
        */
        $factory = new UploadedFileFactory();
        $streamFactory = new StreamFactory();
        foreach ($files as $file) {
            // StreamInterface $stream
            $filePath = $file['tmp_name'];
            $stream = $streamFactory->createStreamFromFile($filePath);
            $size = $file['size'];
            $error = \UPLOAD_ERR_OK;
            $clientFilename = $file['name'];
            $clientMediaType = $file['type'];
            $uploadedFile = $factory->createUploadedFile(
                $stream,
                $size,
                $error,
                $clientFilename,
                $clientMediaType
            );
            array_push($result, $uploadedFile);
        }

        return $result;
    }

    protected function createFilesRequest(
        string $method,
        string $path,
        array $headers = ['HTTP_ACCEPT' => 'application/json'],
        array $cookies = [],
        array $serverParams = [],
        array $files
    ): Request {
        $uri = new Uri('', '', 80, $path);
        $handle = fopen('php://temp', 'w+');
        $stream = (new StreamFactory())->createStreamFromResource($handle);

        $h = new Headers();
        foreach ($headers as $name => $value) {
            $h->addHeader($name, $value);
        }
        $uploadedFiles = $this->toUploadedFileInterface($files);

        return new Request($method, $uri, $h, $cookies, $serverParams, $stream, $uploadedFiles);
    }

    protected function getApi()
    {
        return ApiConstants::API;
    }
}
