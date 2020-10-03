<?php
declare(strict_types=1);

namespace Tests;

use DI\ContainerBuilder;
use Exception;
use PHPUnit\Framework\TestCase as PHPUnit_TestCase;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request as SlimRequest;
use Slim\Psr7\Uri;

use App\Infrastructure\Utils\Properties;
use App\Application\Actions\ActionPayload;
use Psr\Http\Message\ResponseInterface;

class TestCase extends PHPUnit_TestCase
{
    /**
     * @return App
     * @throws Exception
     */
    protected function getAppInstance(): App
    {
        Properties::init(__DIR__ . '/../tests-data', __DIR__ .'/conf.json');

        // Instantiate PHP-DI ContainerBuilder
        $containerBuilder = new ContainerBuilder();

        // Container intentionally not compiled for tests.

        // Set up settings
        $settings = require __DIR__ . '/../app/settings.php';
        $settings($containerBuilder);

        // Set up dependencies
        $dependencies = require __DIR__ . '/../app/dependencies.php';
        $dependencies($containerBuilder);

        // Set up repositories
        $repositories = require __DIR__ . '/../app/repositories.php';
        $repositories($containerBuilder);

        // Build PHP-DI Container instance
        $container = $containerBuilder->build();

        // Instantiate the app
        AppFactory::setContainer($container);
        $app = AppFactory::create();

        // Register middleware
        $middleware = require __DIR__ . '/../app/middleware.php';
        $middleware($app);

        // Register routes
        $routes = require __DIR__ . '/../app/routes.php';
        $routes($app);

        return $app;
        
    }

    /**
     * @param string $method
     * @param string $path
     * @param array  $headers
     * @param array  $cookies
     * @param array  $serverParams
     * @return Request
     */
    protected function createRequest(
        string $method,
        string $path,
        array $headers = ['HTTP_ACCEPT' => 'application/json'],
        array $cookies = [],
        array $serverParams = []
    ): Request {
        $uri = new Uri('', '', 80, $path);
        $handle = fopen('php://temp', 'w+');
        $stream = (new StreamFactory())->createStreamFromResource($handle);

        $h = new Headers();
        foreach ($headers as $name => $value) {
            $h->addHeader($name, $value);
        }

        return new SlimRequest($method, $uri, $h, $cookies, $serverParams, $stream);
    }

    protected function getPublicFile(string $file) : string {
        return file_get_contents(Properties::getInstance()->getPublicDirPath() . $file);
    }

    protected function assertResponse(ActionPayload $expected, ResponseInterface $actual){
        if ($expected->getData() != null ) {
            $jsonResponse = \json_decode((string) $actual->getBody());
            $bodyStr = \json_encode($jsonResponse->{'data'});
    
            $this->assertJsonStringEqualsJsonString($expected->getData(), $bodyStr);
        }

        if ($expected->getError()  != null) {
            $this->assertEquals($expected->getError()->getDescription(), $actual->getReasonPhrase());
        }

        $this->assertEquals($expected->getStatusCode(), $actual->getStatusCode());
    }
}
