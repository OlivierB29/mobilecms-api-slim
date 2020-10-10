<?php
declare(strict_types=1);

namespace Tests\Application\Actions\Cms;

use App\Application\Actions\ActionError;
use App\Application\Actions\ActionPayload;
use App\Application\Handlers\HttpErrorHandler;

use DI\Container;
use Slim\Middleware\ErrorMiddleware;
use Tests\TestCase;

use App\Infrastructure\Utils\Properties;

class ContentPostActionTest extends TestCase
{



    public function testPostBbCode()
    {
        $app = $this->getAppInstance();

        /** @var Container $container */
        $container = $app->getContainer();

        // API
        $request = $this->createRequest('POST', '/mobilecmsapi/v1/cmsapi/content/calendar');

        $contents = \json_decode(file_get_contents('tests-data/public/bbcode.json'));
        if (json_last_error() === JSON_ERROR_NONE) {
            $request = $request->withParsedBody($contents);
        }
      

        $response = $app->handle($request);


        $payloadObject = $response->getBody();
        $payload = (string) $response->getBody();

        // Assert
        $index_data = '{}';

        $expectedPayload = new ActionPayload(200, $index_data);
        $serializedPayload = json_encode($expectedPayload, JSON_PRETTY_PRINT);


        $this->assertResponse($expectedPayload, $response);
    }
    /*
    public function testUpdateBBCode()
    {
        $app = $this->getAppInstance();


        $container = $app->getContainer();

        // API
        $request = $this->createRequest('POST', '/mobilecmsapi/v1/cmsapi/content/calendar');

        $contents = \json_decode($this->getPublicFile('6.json'));
        if (json_last_error() === JSON_ERROR_NONE) {
            $request = $request->withParsedBody($contents);
        }

        $response = $app->handle($request);


        $payloadObject = $response->getBody();
        $payload = (string) $response->getBody();

        // Assert
        $index_data = '{}';

        $expectedPayload = new ActionPayload(200, $index_data);
        $serializedPayload = json_encode($expectedPayload, JSON_PRETTY_PRINT);


        $this->assertResponse($expectedPayload, $response);
    }
    */
}
