<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Cms;

use App\Application\Actions\ActionPayload;
use App\Infrastructure\Utils\FileUtils;
use Tests\AuthApiUtility;

final class WebApiTest extends AuthApiUtility
{
    protected $requestparams = '?timestamp=1599654646';

    protected function setUp(): void
    {
        parent::setUp();
        $this->setGuest();
    }


    public function testGetCalendarList()
    {
        $this->path = $this->getApi().'/webapi/content/calendar'.$this->requestparams;

        $this->GET = ['requestbody' => '{}'];
        $response = $this->request('GET', $this->path);

        $this->printError($response);
        $this->assertEquals(200, $response->getCode());

        $this->assertTrue($response != null);

        $this->assertTrue(strpos($response->getEncodedResult(), 'yet another seminar of activity') !== false);
    }



    public function testGetCalendarRecord()
    {
        $this->path = $this->getApi().'/webapi/content/calendar/1'.$this->requestparams;

        $response = $this->request('GET', $this->path);

        $this->printError($response);
        $this->assertEquals(200, $response->getCode());

        $this->assertTrue($response != null);
        $this->assertTrue($response->getResult() != null);
        $this->assertTrue($response->getResult()->{'id'} === '1');

        $this->assertTrue($response->getResult()->{'type'} === 'calendar');

        $this->assertFalse(empty($response->getResult()->{'date'}));
        $this->assertFalse(empty($response->getResult()->{'title'}));
    }

    public function testGetCalendarError()
    {
        $this->path = $this->getApi().'/webapi/content/calendar/999999999';

        $response = $this->request('GET', $this->path);

        $this->assertEquals(404, $response->getCode());
    }




}
