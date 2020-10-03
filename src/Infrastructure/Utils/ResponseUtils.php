<?php namespace App\Infrastructure\Utils;

use App\Infrastructure\Rest\Response as RestResponse;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Slim\Psr7\Response as ResponseImpl;

class ResponseUtils
{


    public static function toPsrResponse(RestResponse $response): PsrResponse
    {
        $result = new ResponseImpl($response->getCode());
        ///$response->getBody()->write('Hello world!');
        $result->getBody()->write($response->getEncodedResult());

        return $result;
    }


    /**
     * @param  array|object|null $data
     * @return Response
     */
    public static function respondWithData($data = null, int $statusCode = 200, PsrResponse $response): PsrResponse
    {
        $payload = new ActionPayload($statusCode, $data);

        return ResponseUtils::respond($payload);
    }

    /**
     * @param ActionPayload $payload
     * @return Response
     */
    public static function respond(ActionPayload $payload, PsrResponse $response): PsrResponse
    {
        $json = json_encode($payload, JSON_PRETTY_PRINT);
        $response->getBody()->write($json);

        return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus($payload->getStatusCode());
    }
}
