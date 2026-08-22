<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Cms;

use Tests\AuthApiUtility;

class ContentPostActionTest extends AuthApiUtility
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setEditor();

    }


    public function testPostGeneratesIdInResponse()
    {
        $this->path = $this->getApi().'/cmsapi/content/news';
        $this->POST = ['requestbody' => '{"status":"draft","title":"generated from post","media":[],"date":"2026-08-22","activity":"kendo","description":"<p>aaaaaa</p>"}'];
        $response = $this->request('POST', $this->path);

        $this->assertEquals(200, $response->getCode());
        $this->assertEquals('2026-generated-from-post', $response->getResult()->id);

        $file = $this->API->getPublicDirPath().'/news/2026-generated-from-post.json';
        $this->assertFileExists($file);
        unlink($file);
    }
    
}
