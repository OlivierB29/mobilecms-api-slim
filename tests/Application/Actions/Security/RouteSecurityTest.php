<?php

declare(strict_types=1);
namespace Tests\Application\Actions\Security;

use App\Application\Actions\ActionError;
use App\Application\Actions\ActionPayload;
use App\Application\Handlers\HttpErrorHandler;

use DI\Container;
use Slim\Middleware\ErrorMiddleware;

use Tests\ApiTest;
use App\Infrastructure\Utils\Properties;

use App\Infrastructure\Utils\FileUtils;

final class RouteSecurityTest extends ApiTest
{
    protected $requestparams = '?timestamp=1599654646';

    protected function setUp(): void
    {
        parent::setUp();
    }


    public function testXssRoute1()
    {
        $xss = '<script>alert("foo")</script>';
        $this->path = $this->getApi() . '/authapi/publicinfo' . '?' . $xss;

        $response = $this->request('POST', $this->path);


        $this->assertEquals(400, $response->getCode());
        $this->assertTrue($response != null);

//        $this->assertJsonStringEqualsJsonString('{"error":"wrong role"}', $response->getEncodedResult());
    }

    public function testXssRoute2()
    {
        $xss = 'foo=bar';
        $this->path = $this->getApi() . '/authapi/publicinfo' . '?' . $xss;

        $response = $this->request('POST', $this->path);


        $this->assertEquals(400, $response->getCode());
        $this->assertTrue($response != null);

//        $this->assertJsonStringEqualsJsonString('{"error":"wrong role"}', $response->getEncodedResult());
    }
    
    public function testXssRoute3()
    {
        $xss = 'request=phpinfo()';
        $this->path = $this->getApi() . '/authapi/publicinfo' . '?' . $xss;

        $response = $this->request('POST', $this->path);


        $this->assertEquals(400, $response->getCode());
        $this->assertTrue($response != null);

//        $this->assertJsonStringEqualsJsonString('{"error":"wrong role"}', $response->getEncodedResult());
    }
}
