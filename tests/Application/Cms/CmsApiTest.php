<?php

declare(strict_types=1);
namespace Tests\Application\Actions\Cms;
use App\Application\Actions\ActionError;
use App\Application\Actions\ActionPayload;
use App\Application\Handlers\HttpErrorHandler;

use DI\Container;
use Slim\Middleware\ErrorMiddleware;
use Tests\TestCase;
use Tests\ApiTest;
use App\Infrastructure\Utils\Properties;

use App\Infrastructure\Utils\FileUtils;

final class CmsApiTest extends ApiTest
{
    protected $requestparams = '?timestamp=1599654646';


    public function testTypes()
    {
        $this->path = '/mobilecmsapi/v1/cmsapi/content';
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

    public function testPostSuccess()
    {

        // echo 'testPostSuccess: ' . $this->memory();
        $this->path = '/mobilecmsapi/v1/cmsapi/content/calendar';


        $recordStr = file_get_contents($this->API->getPublicDirPath() . '/big.json');
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
        $request = $this->createRequest('GET', '/mobilecmsapi/v1/cmsapi/index/calendar');
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
        $this->path = '/mobilecmsapi/v1/cmsapi/content/calendar';


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
        $this->path = '/mobilecmsapi/v1/cmsapi/content/calendar';


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
        $this->path = '/mobilecmsapi/v1/cmsapi/content/calendar';


        $recordStr = file_get_contents($this->API->getPublicDirPath() . '/6.json');
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
        $this->path = '/mobilecmsapi/v1/cmsapi/content/calendar';
        $this->headers=['Authorization' => ''];



        $this->GET = ['requestbody' => '{}'];
        $response = $this->request('GET', $this->path);

        $this->assertEquals(401, $response->getCode());
    }

    public function testGetCalendarList()
    {
        $this->path = '/mobilecmsapi/v1/cmsapi/content/calendar' . $this->requestparams;


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
        $this->path = '/mobilecmsapi/v1/cmsapi/content/calendar/1';

        $response = $this->request('GET', $this->path);


        $this->assertEquals(403, $response->getCode());
        $this->assertTrue($response != null);

        $this->assertJsonStringEqualsJsonString('{"error":"wrong role"}', $response->getEncodedResult());
    }

    public function testGetCalendarRecord()
    {
        $this->path = '/mobilecmsapi/v1/cmsapi/content/calendar/1'. $this->requestparams;


        $response = $this->request('GET', $this->path);


        $this->printError($response);
        $this->assertEquals(200, $response->getCode());

        $this->assertTrue($response != null);
        $this->assertTrue($response->getResult() != null);
        if(array_key_exists('id', $response->getResult())) {
            $this->assertTrue($response->getResult()->{'id'} === '1');
        }
       
        $this->assertTrue($response->getResult()->{'type'} === 'calendar');

        $this->assertFalse(empty($response->getResult()->{'date'}));
        $this->assertFalse(empty($response->getResult()->{'title'}));
    }

    public function testGetCalendarError()
    {
        $this->path = '/mobilecmsapi/v1/cmsapi/content/calendar/999999999';

        $response = $this->request('GET', $this->path);


        $this->assertEquals(404, $response->getCode());
    }

    public function testDelete()
    {
        $id = 'exampleid';


        //clone backup to directory
        $recordfile = $this->API->getPublicDirPath() . '/calendar/' . $id . '.json';
        copy($this->API->getPublicDirPath() . '/calendar/backup/' . $id . '.json', $recordfile);

        $fileutils = new FileUtils();
        $mediadir = $this->API->getMediaDirPath() . '/calendar/' . $id;
        $fileutils->copydir($this->API->getMediaDirPath() . '/calendar/backup/' . $id, $mediadir);

        $this->path = '/mobilecmsapi/v1/cmsapi/content/calendar/' . $id;



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
            $recordfile = $this->API->getPublicDirPath() . '/calendar/' . $id . '.json';
            copy($this->API->getPublicDirPath() . '/calendar/backuplist/' . $id . '.json', $recordfile);
        }

        $this->path = '/mobilecmsapi/v1/cmsapi/deletelist/calendar';


        $this->POST = ['requestbody' => \json_encode($ids)];
        $response = $this->request('POST', $this->path);


        $this->assertEquals(200, $response->getCode());
        $this->assertTrue($response != null);

        foreach ($ids as $id) {
            $recordfile = $this->API->getPublicDirPath() . '/calendar/' . $id . '.json';
            $this->assertTrue(!file_exists($recordfile));

            $mediadir = $this->API->getMediaDirPath() . '/calendar/' . $id;
            $this->assertTrue(!is_dir($mediadir));
        }

        $this->assertJsonStringEqualsJsonString('{}', $response->getEncodedResult());
    }



    public function testGetIndex()
    {
        $this->path = '/mobilecmsapi/v1/cmsapi/index/calendar' . $this->requestparams;


        $response = $this->request('GET', $this->path);


        $this->printError($response);
        $this->assertEquals(200, $response->getCode());

        $this->assertTrue($response != null);
        $index_data = file_get_contents($this->API->getPublicDirPath() . '/calendar/index/index.json');

        $this->assertJsonStringEqualsJsonString($index_data, $response->getEncodedResult());
    }

    public function testGetMetadata()
    {
        $this->path = '/mobilecmsapi/v1/cmsapi/metadata/calendar';


        $response = $this->request('GET', $this->path);


        $this->printError($response);
        $this->assertEquals(200, $response->getCode());

        $this->assertTrue($response != null);
        $index_data = file_get_contents($this->API->getPublicDirPath() . '/calendar/index/metadata.json');

        $this->assertJsonStringEqualsJsonString($index_data, $response->getEncodedResult());
    }

    public function testTemplate()
    {
        $this->path = '/mobilecmsapi/v1/cmsapi/template/calendar';


        $response = $this->request('GET', $this->path);


        $this->printError($response);
        $this->assertEquals(200, $response->getCode());

        $this->assertTrue($response != null);
        $index_data = file_get_contents($this->API->getPublicDirPath() . '/calendar/index/new.json');

        $this->assertJsonStringEqualsJsonString($index_data, $response->getEncodedResult());
    }

    public function testStatus()
    {
        $this->path = '/mobilecmsapi/v1/cmsapi/status';
        $this->SERVER = ['REQUEST_URI' => $this->path,    'REQUEST_METHOD' => 'GET', 'HTTP_ORIGIN' => 'foobar'];
        $response = $this->request('GET', $this->path);

        $this->assertTrue($response != null);
        $this->assertJsonStringEqualsJsonString('{"result":"true"}', $response->getEncodedResult());
        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
    }
}
