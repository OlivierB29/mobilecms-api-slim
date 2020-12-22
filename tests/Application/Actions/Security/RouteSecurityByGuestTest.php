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

final class RouteSecurityByGuestTest extends AuthApiTest
{
    protected $requestparams = '?timestamp=1599654646';

    protected function setUp(): void
    {
        parent::setUp();
        $this->setGuest();
    }


    public function testXssByGuest1()
    {
        $xss = '<script>alert("foo")</script>';
        $this->path = $this->getApi() . '/cmsapi/content/calendar/1' . '?' . $xss;

        $response = $this->request('GET', $this->path);


        $this->assertEquals(403, $response->getCode());
        $this->assertTrue($response != null);

//        $this->assertJsonStringEqualsJsonString('{"error":"wrong role"}', $response->getEncodedResult());
    }

    public function testXssByGuest2()
    {
        $xss = 'foo=bar';
        $this->path = $this->getApi() . '/cmsapi/content/calendar/1' . '?' . $xss;

        $response = $this->request('GET', $this->path);


        $this->assertEquals(403, $response->getCode());
        $this->assertTrue($response != null);

//        $this->assertJsonStringEqualsJsonString('{"error":"wrong role"}', $response->getEncodedResult());
    }
    
    public function testXssByGuest3()
    {
        $xss = 'request=phpinfo()';
        $this->path = $this->getApi() . '/cmsapi/content/calendar/1' . '?' . $xss;

        $response = $this->request('GET', $this->path);


        $this->assertEquals(403, $response->getCode());
        $this->assertTrue($response != null);

//        $this->assertJsonStringEqualsJsonString('{"error":"wrong role"}', $response->getEncodedResult());
    }
}
