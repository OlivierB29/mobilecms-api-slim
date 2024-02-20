<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Security;

use Tests\AuthApiTest;

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
        $this->path = $this->getApi().'/cmsapi/content/calendar/1'.'?'.$xss;

        $response = $this->request('GET', $this->path);

        $this->assertEquals(401, $response->getCode());
        $this->assertTrue($response != null);
    }

    public function testXssByGuest2()
    {
        $xss = 'foo=bar';
        $this->path = $this->getApi().'/cmsapi/content/calendar/1'.'?'.$xss;

        $response = $this->request('GET', $this->path);

        $this->assertEquals(401, $response->getCode());
        $this->assertTrue($response != null);
    }

    public function testXssByGuest3()
    {
        $xss = 'request=phpinfo()';
        $this->path = $this->getApi().'/cmsapi/content/calendar/1'.'?'.$xss;

        $response = $this->request('GET', $this->path);

        $this->assertEquals(401, $response->getCode());
        $this->assertTrue($response != null);
    }
}
