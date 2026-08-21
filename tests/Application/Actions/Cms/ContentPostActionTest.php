<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Cms;

use App\Application\Actions\ActionPayload;
use DI\Container;
use Tests\AuthApiUtility;

class ContentPostActionTest extends AuthApiUtility
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setEditor();

    }


    public function testPostText()
    {
        $app = $this->getAppInstance();

        /** @var Container $container */
        $container = $app->getContainer();

        // API
        $request = $this->createRequest('POST', $this->getApi().'/cmsapi/content/calendar', $this->headers);

        $contents = \json_decode(file_get_contents('tests-data/public/text.json'));
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
    
}
