<?php
declare(strict_types=1);

namespace Tests\Application\Actions\Cms;

use App\Application\Actions\ActionError;
use App\Application\Actions\ActionPayload;
use App\Application\Handlers\HttpErrorHandler;

use DI\Container;
use Slim\Middleware\ErrorMiddleware;
use Tests\AuthApiTest;

use App\Infrastructure\Utils\Properties;

class IndexGetActionTest extends AuthApiTest
{
    public function testGetIndex()
    {
        $app = $this->getAppInstance();

        /** @var Container $container */
        $container = $app->getContainer();

        // API
        $request = $this->createRequest('GET', '/mobilecmsapi/v1/cmsapi/index/calendar', $this->headers);
        $response = $app->handle($request);
        $payloadObject = $response->getBody();
        $payload = (string) $response->getBody();

        // Assert
        $index_data = $this->getPublicFile('/calendar/index/index.json');

        $expectedPayload = new ActionPayload(200, $index_data);
        $serializedPayload = json_encode($expectedPayload, JSON_PRETTY_PRINT);


        $this->assertResponse($expectedPayload, $response);
    }

    public function testIndexNotFound()
    {
        $app = $this->getAppInstance();

        $callableResolver = $app->getCallableResolver();
        $responseFactory = $app->getResponseFactory();

        $errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);
        $errorMiddleware = new ErrorMiddleware($callableResolver, $responseFactory, true, false, false);
        $errorMiddleware->setDefaultErrorHandler($errorHandler);

        $app->add($errorMiddleware);

        // API
        $request = $this->createRequest('GET', '/mobilecmsapi/v1/cmsapi/index/calendarZZ', $this->headers);
        $response = $app->handle($request);

        // Assert
        $expectedError = new ActionError(ActionError::RESOURCE_NOT_FOUND, 'Internal Server Error');
        $expectedPayload = new ActionPayload(500, null, $expectedError);

        $this->assertResponse($expectedPayload, $response);
    }
}
