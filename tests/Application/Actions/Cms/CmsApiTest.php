<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Cms;

use App\Application\Actions\ActionPayload;
use App\Infrastructure\Utils\FileUtils;
use Tests\AuthApiUtility;

final class CmsApiTest extends AuthApiUtility
{
    
    protected $requestparams = '?timestamp=1599654646';

    protected function setUp(): void
    {
        parent::setUp();
        $this->setAdmin();
    }

    public function testTypes()
    {
        $this->path = $this->getApi().'/cmsapi/content';
        $this->SERVER = ['REQUEST_URI' => $this->path,    'REQUEST_METHOD' => 'GET', 'HTTP_ORIGIN' => 'foobar'];
        $response = $this->request('GET', $this->path);

        $this->assertTrue($response != null);
        $this->assertJsonStringEqualsJsonString('[
          {"type":"calendar", "labels": [ {"i18n":"en", "label":"Calendar"}, {"i18n":"fr", "label":"Calendrier"}]},
          {"type":"news", "labels": [ {"i18n":"en", "label":"News"}, {"i18n":"fr", "label":"Actualités"}]}
        ]', $response->getEncodedResult());
        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
    }

    public function testAgendaIcs()
    {
        $response = $this->getAppInstance()->handle($this->createRequest('GET', '/mobilecmsapi/agenda/events.ics'));
        $calendar = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSame('text/calendar; charset=utf-8', $response->getHeaderLine('Content-Type'));
        $this->assertStringContainsString('BEGIN:VCALENDAR', $calendar);
        $this->assertStringContainsString('UID:1@mobilecms', $calendar);
        $this->assertStringContainsString('URL:http://localhost:5173/#/calendrier/detail/1', $calendar);
        $this->assertStringContainsString('DTSTART;VALUE=DATE:20150901', $calendar);
        $this->assertStringContainsString('SUMMARY:some seminar of activity A', $calendar);
        $this->assertStringEndsWith("END:VCALENDAR\r\n", $calendar);
    }

    public function testPostSuccess()
    {
        // echo 'testPostSuccess: ' . $this->memory();
        $this->path = $this->getApi().'/cmsapi/content/calendar';

        $recordStr = file_get_contents($this->API->getPublicDirPath().'/big.json');
        $this->POST = ['requestbody' => $recordStr];
        unset($recordStr);
        $response = $this->request('POST', $this->path);

        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
        // echo 'processAPI: ' . $this->memory();
        $this->assertTrue($response->getResult() != null && $response->getResult() != '');
    }

    /*
     public function testGetIndex()
    {
        $app = $this->getAppInstance();

        $container = $app->getContainer();

        // API
        $request = $this->createRequest('GET', $this->getApi() . '/cmsapi/index/calendar');
        $response = $app->handle($request);
        $payloadObject = $response->getBody();
        $payload = (string) $response->getBody();

        // Assert
        $index_data = $this->getPublicFile('/calendar/index/index.json');

        $expectedPayload = new ActionPayload(200, $index_data);
        $serializedPayload = json_encode($expectedPayload, JSON_PRETTY_PRINT);


        $this->assertResponse($expectedPayload, $response);
    }

    */

    public function testPostHtml()
    {
        // echo 'testPostSuccess: ' . $this->memory();
        $this->path = $this->getApi().'/cmsapi/content/calendar';

        $recordStr = file_get_contents('tests-data/public/html.json');
        $this->POST = ['requestbody' => $recordStr];
        unset($recordStr);
        $response = $this->request('POST', $this->path);

        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
        // echo 'processAPI: ' . $this->memory();
        $this->assertTrue($response->getResult() != null && $response->getResult() != '');

        //TODO assert result without HTML
    }

    public function testPostBBCode()
    {
        // echo 'testPostSuccess: ' . $this->memory();
        $this->path = $this->getApi().'/cmsapi/content/calendar';

        $recordStr = file_get_contents('tests-data/public/bbcode.json');
        $this->POST = ['requestbody' => $recordStr];
        unset($recordStr);
        $response = $this->request('POST', $this->path);

        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
        // echo 'processAPI: ' . $this->memory();
        $this->assertTrue($response->getResult() != null && $response->getResult() != '');
    }

    public function testUpdateBBCode()
    {
        // echo 'testPostSuccess: ' . $this->memory();
        $this->path = $this->getApi().'/cmsapi/content/calendar';

        $recordStr = file_get_contents($this->API->getPublicDirPath().'/6.json');
        $this->POST = ['requestbody' => $recordStr];
        unset($recordStr);
        $response = $this->request('POST', $this->path);

        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
        // echo 'processAPI: ' . $this->memory();
        $this->assertTrue($response->getResult() != null && $response->getResult() != '');
    }

    public function testEmptyToken()
    {
        $this->path = $this->getApi().'/cmsapi/content/calendar';
        $this->headers['Authorization'] = '';

        $this->GET = ['requestbody' => '{}'];
        $response = $this->request('GET', $this->path);

        $this->assertEquals(401, $response->getCode());
    }

    public function testGetCalendarList()
    {
        $this->path = $this->getApi().'/cmsapi/content/calendar'.$this->requestparams;

        $this->GET = ['requestbody' => '{}'];
        $response = $this->request('GET', $this->path);

        $this->printError($response);
        $this->assertEquals(200, $response->getCode());

        $this->assertTrue($response != null);

        $this->assertTrue(strpos($response->getEncodedResult(), '{"filename":"1.json","id":"1"}') !== false);
    }

    public function testGetByGuest()
    {
        $this->setGuest();
        $this->path = $this->getApi().'/cmsapi/content/calendar/1';

        $response = $this->request('GET', $this->path);

        $this->assertEquals(403, $response->getCode());
        $this->assertTrue($response != null);

//        $this->assertJsonStringEqualsJsonString('{"error":"wrong role"}', $response->getEncodedResult());
    }

    public function testGetCalendarRecord()
    {
        $this->path = $this->getApi().'/cmsapi/content/calendar/1'.$this->requestparams;

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
        $this->path = $this->getApi().'/cmsapi/content/calendar/999999999';

        $response = $this->request('GET', $this->path);

        $this->assertEquals(404, $response->getCode());
    }

    public function testDelete()
    {
        $id = 'exampleid';

        //clone backup to directory
        $recordfile = $this->API->getPublicDirPath().'/calendar/'.$id.'.json';
        copy($this->API->getPublicDirPath().'/calendar/backup/'.$id.'.json', $recordfile);

        $fileutils = new FileUtils();
        $mediadir = $this->API->getMediaDirPath().'/calendar/'.$id;
        $fileutils->copydir($this->API->getMediaDirPath().'/calendar/backup/'.$id, $mediadir);

        $this->path = $this->getApi().'/cmsapi/content/calendar/'.$id;

        $response = $this->request('DELETE', $this->path);

        $this->assertEquals(200, $response->getCode());
        $this->assertTrue($response != null);

        $this->assertTrue(!file_exists($recordfile));
        $this->assertTrue(!is_dir($mediadir));

        $this->assertJsonStringEqualsJsonString('{}', $response->getEncodedResult());
    }

    public function testDeleteList()
    {
        //clone backup to directory
        $ids = ['101', '102', '103'];
        foreach ($ids as $id) {
            $recordfile = $this->API->getPublicDirPath().'/calendar/'.$id.'.json';
            copy($this->API->getPublicDirPath().'/calendar/backuplist/'.$id.'.json', $recordfile);
        }

        $this->path = $this->getApi().'/cmsapi/deletelist/calendar';

        $this->POST = ['requestbody' => \json_encode($ids)];
        $response = $this->request('POST', $this->path);

        $this->assertEquals(200, $response->getCode());
        $this->assertTrue($response != null);

        foreach ($ids as $id) {
            $recordfile = $this->API->getPublicDirPath().'/calendar/'.$id.'.json';
            $this->assertTrue(!file_exists($recordfile));

            $mediadir = $this->API->getMediaDirPath().'/calendar/'.$id;
            $this->assertTrue(!is_dir($mediadir));
        }

        $this->assertJsonStringEqualsJsonString('{}', $response->getEncodedResult());
    }

    public function testGetIndex()
    {
        $this->path = $this->getApi().'/cmsapi/index/calendar'.$this->requestparams;

        $response = $this->request('GET', $this->path);

        $this->printError($response);
        $this->assertEquals(200, $response->getCode());

        $this->assertTrue($response != null);
        $index_data = file_get_contents($this->API->getPublicDirPath().'/calendar/index/index.json');

        $this->assertJsonStringEqualsJsonString($index_data, $response->getEncodedResult());
    }

    public function testPostRebuildIndex()
    {
        $this->path = $this->getApi().'/cmsapi/index/calendar'.$this->requestparams;

        $response = $this->request('POST', $this->path);

        $this->assertEquals(200, $response->getCode());
    }

    public function testGetMetadata()
    {
        $this->path = $this->getApi().'/cmsapi/metadata/calendar';

        $response = $this->request('GET', $this->path);

        $this->printError($response);
        $this->assertEquals(200, $response->getCode());

        $this->assertTrue($response != null);
        $index_data = file_get_contents($this->API->getPublicDirPath().'/calendar/index/metadata.json');

        $this->assertJsonStringEqualsJsonString($index_data, $response->getEncodedResult());
    }

    public function testTemplate()
    {
        $this->path = $this->getApi().'/cmsapi/template/calendar';

        $response = $this->request('GET', $this->path);

        $this->printError($response);
        $this->assertEquals(200, $response->getCode());

        $this->assertTrue($response != null);
        $index_data = file_get_contents($this->API->getPublicDirPath().'/calendar/index/new.json');

        $this->assertJsonStringEqualsJsonString($index_data, $response->getEncodedResult());
    }

    public function testStatus()
    {
        $this->path = $this->getApi().'/cmsapi/status';
        $this->SERVER = ['REQUEST_URI' => $this->path,    'REQUEST_METHOD' => 'GET', 'HTTP_ORIGIN' => 'foobar'];
        $response = $this->request('GET', $this->path);

        $this->assertTrue($response != null);
        $this->assertJsonStringEqualsJsonString('{}', $response->getEncodedResult());
        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
    }
}
