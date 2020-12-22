<?php

declare(strict_types=1);
namespace Tests\Application\Actions\Security;

use Tests\ApiTest;

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
        $this->path = $this->getApi() . '/debugapi' . '?' . $xss;

        $response = $this->request('GET', $this->path);


        $this->assertEquals(200, $response->getCode());
        $this->assertTrue($response != null);

        $this->assertJsonStringEqualsJsonString('{"uri": "/mobilecmsapi/v2/debugapi"}', $response->getEncodedResult());
    }

    public function testXssRoute2()
    {
        $xss = 'foo=bar';
        $this->path = $this->getApi() . '/debugapi' . '?' . $xss;

        $response = $this->request('GET', $this->path);


        $this->assertEquals(200, $response->getCode());
        $this->assertTrue($response != null);

        $this->assertJsonStringEqualsJsonString('{"uri": "/mobilecmsapi/v2/debugapi"}', $response->getEncodedResult());
    }
    
    public function testXssRoute3()
    {
        $xss = 'request=phpinfo()';
        $this->path = $this->getApi() . '/debugapi' . '?' . $xss;

        $response = $this->request('GET', $this->path);


        $this->assertEquals(200, $response->getCode());
        $this->assertTrue($response != null);

        $this->assertJsonStringEqualsJsonString('{"uri": "/mobilecmsapi/v2/debugapi"}', $response->getEncodedResult());
    }
}
