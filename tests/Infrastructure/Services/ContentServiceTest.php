<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Services;

use App\Infrastructure\Services\ContentService;
use Tests\ApiUtility;


final class ContentServiceTest extends ApiUtility
{
    private $dir = 'tests-data/public';

    private $indexfile = 'calendar/index/index.json';

    public function testGetAll()
    {
        $service = new ContentService($this->dir);
        $response = $service->getAll($this->indexfile);

        $this->assertEquals(200, $response->getCode());

        $this->assertTrue(
            strstr($response->getEncodedResult(), '"id":"1"') != ''
        );

        $this->assertTrue(
            strstr($response->getEncodedResult(), '"id":"2"') != ''
        );
    }

    public function testGetItemFromList()
    {
        $service = new ContentService($this->dir);
        $response = $service->get($this->indexfile, 'id', '1');

        $this->assertEquals(200, $response->getCode());

        $this->assertJsonStringEqualsJsonString(
            '{ "id": "1","date": "2015-09-01", "activity": "activitya", "title": "some seminar of activity A"}',
            $response->getEncodedResult()
        );
    }

    public function testPostEmptyType()
    {
        $this->expectException(\Exception::class);
        $recordStr = '{"id":"10","date":"2015-09-01","activity":"activitya","title":"some seminar of activity A","organization":"Some org","description":"<some infos","url":"","location":"","startdate":"","enddate":"","updated":"","updatedby":""}';
        $service = new ContentService($this->dir);
        $response = $service->post('', 'id', json_decode($recordStr));
    }



    public function testPostEmptyRecord()
    {
        $recordStr = '{}';
        $service = new ContentService($this->dir);
        $response = $service->post('calendar', 'id', json_decode($recordStr));

        $file = $this->dir.'/calendar/10.json';
        
        $this->assertEquals(400, $response->getCode());
    }

    public function testPostGeneratesIdFromMetadata()
    {
        $record = json_decode('{"title":"aaaaaaaaaa","media":[],"date":"2026-08-23","activity":"kendo","description":"<p>aaaaaa</p>"}');
        $service = new ContentService($this->dir);
        $response = $service->post('news', 'id', $record);

        $this->assertEquals(200, $response->getCode());
        $this->assertEquals('2026-aaaaaaaaaa', $response->getResult()->id);

        $file = $this->dir.'/news/2026-aaaaaaaaaa.json';
        $this->assertFileExists($file);

        $collision = json_decode('{"status":"draft","title":"aaaaaaaaaa","media":[],"date":"2026-08-23","activity":"kendo","description":"<p>aaaaaa</p>"}');
        $collisionResponse = $service->post('news', 'id', $collision);
        $this->assertEquals(200, $collisionResponse->getCode());
        $this->assertEquals('2026-aaaaaaaaaa-2', $collisionResponse->getResult()->id);

        unlink($file);
        unlink($this->dir.'/news/2026-aaaaaaaaaa-2.json');
    }

    public function testPostKeepsClientIdWhenProvided()
    {
        $recordStr = '{"id":"client-id-keep","date":"2026-08-23","title":"aaaaaaaaaa","status":"draft"}';
        $service = new ContentService($this->dir);
        $response = $service->post('news', 'id', json_decode($recordStr));

        $this->assertEquals(200, $response->getCode());
        $this->assertEquals('client-id-keep', $response->getResult()->id);

        $file = $this->dir.'/news/client-id-keep.json';
        $this->assertFileExists($file);
        unlink($file);
    }

    public function testBasicPost()
    {
        $recordStr = '{"id":"10","date":"2015-09-01","activity":"activitya","title":"some seminar of activity A","organization":"Some org","description":"<some infos","url":"","location":"","startdate":"","enddate":"","updated":"","updatedby":""}';
        $service = new ContentService($this->dir);
        $response = $service->post('calendar', 'id', json_decode($recordStr));

        $file = $this->dir.'/calendar/10.json';

        $this->assertEquals(200, $response->getCode());

        $this->assertJsonStringEqualsJsonFile($file, $recordStr);
    }

    public function testPostWithIndexMostRecent()
    {
        $recordStr = '{"id":"210","date":"2020-09-01","activity":"activitya","title":"some seminar of activity A","organization":"Some org","description":"<some infos","url":"","location":"","startdate":"","enddate":"","updated":"","updatedby":""}';
        $service = new ContentService('tests-data2/public1');
        $response = $service->post('calendar', 'id', json_decode($recordStr));

        $file = 'tests-data2/public1'.'/calendar/210.json';

        $this->assertEquals(200, $response->getCode());

        $this->assertJsonStringEqualsJsonFile($file, $recordStr);

        $response = $service->publishById('calendar', 'id', '210');
    }

    public function testPostWithIndexMiddle()
    {
        $recordStr = '{"id":"220","date":"2016-11-20","activity":"activitya","title":"some seminar of activity A","organization":"Some org","description":"some infos","url":"","location":"","startdate":"","enddate":"","updated":"","updatedby":""}';
        $service = new ContentService('tests-data2/public2');
        $response = $service->post('calendar', 'id', json_decode($recordStr));

        $file = 'tests-data2/public2'.'/calendar/220.json';

        $this->assertEquals(200, $response->getCode());

        $this->assertJsonStringEqualsJsonFile($file, $recordStr);

        $response = $service->publishById('calendar', 'id', '220');
    }

    public function testUpdate()
    {
        $recordStr = '{"id":"5","type":"calendar","date":"2015-09-01","activity":"activitya","title":"some seminar of activity A","organization":"Some org","description":"some infos","url":"","location":"","startdate":"","enddate":"","updated":"","updatedby":""}';
        $service = new ContentService($this->dir);
        $response = $service->update('calendar', 'id', json_decode($recordStr));

        $file = $this->dir.'/calendar/5.json';

        $this->assertEquals(200, $response->getCode());

        $this->assertJsonStringEqualsJsonFile($file, $recordStr);
    }

    public function testPublish()
    {
        $service = new ContentService($this->dir);
        $response = $service->publishById('calendar', 'id', '10');

        if ($response->getCode() !== 200) {
            echo $response->getResult();
            echo $response->getMessage();
        }
        
        $this->assertEquals(200, $response->getCode());
    }

    public function testRebuildIndex()
    {
        $service = new ContentService($this->dir);
        $response = $service->rebuildIndex('calendar', 'id');

        $this->assertEquals(200, $response->getCode());
    }
}
