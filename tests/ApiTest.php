<?php
namespace Tests;

use Tests\TestCase;
use Tests\FakeApi;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use App\Infrastructure\Rest\Response;
// reminder : PHPUnit autoloader seems to import files with an alphabetic order.

abstract class ApiTest extends TestCase
{
    protected $path='';
    protected $headers=[];
    protected $REQUEST=[];
    protected $SERVER=[];
    protected $GET=[];
    protected $POST=[];

    protected $memory1 = 0;
    protected $memory2 = 0;

    protected $API;


    protected function setUp(): void
    {
        $this->path='';
        $this->headers=['HTTP_ACCEPT' => 'application/json'];
        $this->REQUEST=[];
        $this->SERVER=[];
        $this->GET=[];
        $this->POST=[];
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
        if ($response->getCode() != 200) {
            echo 'ERROR ' . $response->getEncodedResult();
        }
    }

        /**
     * execute request throw slim, using previous class Response
     */
    protected function request($verb, $pathArg): Response
    {
        $path = '';
        // ignore request parameters : TODO ignore them into Slim
        if (strpos($pathArg, "?") !== false) {
            $path =  substr($pathArg, 0, strpos($pathArg, "?"));
        } else {
            $path = $pathArg;
        }

        // request with verb and path
       // $token = 'TEST';
      //  $headers = ['HTTP_ACCEPT' => 'application/json', 'Authorization' => 'Bearer ' . $token];
        $request = $this->createRequest($verb, $path, $this->headers);
        // emulate POST body
        if (\array_key_exists('requestbody', $this->POST)) {

            $contents = \json_decode($this->POST['requestbody']);
            if (json_last_error() === JSON_ERROR_NONE) {
                $request = $request->withParsedBody($contents);
            }


            // $body = new StringStream(json_encode(['tasks' => ['Code','Coffee', ]]));;
          /*  $body = new StringStream($this->POST['requestbody']);;
            $request = $request->withHeader('Content-Type', 'application/json')->withBody($body);
        */
        }
        $app = $this->getAppInstance();
        // execute
        $response = $app->handle($request);

        return $this->toOldResponse($response);


    }


    /**
     * convert a PSR to the previous Response class
     */
    protected function toOldResponse(PsrResponse $psrResponse ) : Response {
        $result = new Response();
        $result->setCode($psrResponse->getStatusCode());

        $jsonResponse = \json_decode((string) $psrResponse->getBody());
        //$body = \json_encode($jsonResponse->{'data'});
        if (\array_key_exists('data', $jsonResponse)) {
            $body = $jsonResponse->{'data'};
            $result->setResult($body);
        } else {
            $result->setResult(\json_decode('{}'));
        }

        
        return $result;
    }


}
