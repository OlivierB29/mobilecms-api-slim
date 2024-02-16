<?php

declare(strict_types=1);
namespace Tests\Application\Actions\File;

use App\Application\Actions\ActionError;
use App\Application\Actions\ActionPayload;
use App\Application\Handlers\HttpErrorHandler;

use DI\Container;
use Slim\Middleware\ErrorMiddleware;

use Tests\AuthApiTest;
use App\Infrastructure\Utils\Properties;

use App\Infrastructure\Utils\FileUtils;

final class FileApiTest extends AuthApiTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setAdmin();
    }

    /*
        public function testDownload()
        {
            $this->path = $this->getApi() . '/fileapi/download/calendar/4';
            $this->SERVER = ['REQUEST_URI' => $this->path, 'REQUEST_METHOD' => 'POST', 'HTTP_ORIGIN' => 'foobar'];

            $recordStr = '[{ "url": "https://mit-license.org/index.html", "title":"MIT licence"}]';

            $this->POST = ['requestbody' => $recordStr];
            unset($recordStr);





            $response = $this->request('POST', $this->path);

            $this->printError($response);
            $this->assertEquals(200, $response->getCode());

            $this->assertTrue($response != null);

            // test JSON response
            $this->assertTrue(count($response->getResult()) === 1);
            $imageData = $response->getResult()[0];
            $this->assertTrue($imageData->{'url'} === 'index.html');
            $this->assertTrue($imageData->{'title'} === 'MIT licence');


            // test download
            $download = file_get_contents($this->API->getMediaDirPath() . '/calendar/4/index.html');
            $this->assertTrue(strpos($download, 'MIT License') !== false);
            $fileutil = new \mobilecms\utils\FileUtils();
            $fileutil->deleteDir($this->API->getMediaDirPath() . '/calendar/4');
        }

        public function testDownloadImage()
        {
            $this->path = $this->getApi() . '/fileapi/download/calendar/5';
            $this->SERVER = ['REQUEST_URI' => $this->path, 'REQUEST_METHOD' => 'POST', 'HTTP_ORIGIN' => 'foobar'];

            $recordStr = '[{ "url": "https://php.net/images/logos/new-php-logo.png", "title":"php logo"}]';

            $this->POST = ['requestbody' => $recordStr];
            unset($recordStr);



                    $response = $this->request('POST', $this->path);



            $this->printError($response);
            $this->assertEquals(200, $response->getCode());

            $this->assertTrue($response != null);

            // test JSON response
            $this->assertTrue(count($response->getResult()) === 1);
            $imageData = $response->getResult()[0];
            $this->assertTrue($imageData->{'url'} === 'new-php-logo.png');
            $this->assertTrue($imageData->{'title'} === 'php logo');

            // test download
            $this->assertTrue(\file_exists($this->API->getMediaDirPath() . '/calendar/5/new-php-logo.png'));
            $fileutil = new \mobilecms\utils\FileUtils();
            $fileutil->deleteDir($this->API->getMediaDirPath() . '/calendar/5');
        }


        public function testDownloadNoFiles()
        {
            $this->path = $this->getApi() . '/fileapi/download/calendar/4';
            $this->SERVER = ['REQUEST_URI' => $this->path, 'REQUEST_METHOD' => 'POST', 'HTTP_ORIGIN' => 'foobar'];
            $recordStr = '[{ "url": "/foobar/foo.html", "title":"foobar"}]';
            $this->POST = ['requestbody' => $recordStr];
            unset($recordStr);


            $response = $this->request('POST', $this->path);
            $this->assertEquals(500, $response->getCode());
        }
    */
    public function testDelete()
    {
        $filename = 'blackholeaccretion_w1920_h1080_cw1920_ch1080.jpg';
        $record = '/calendar/2';
        // tests-data/fileapi/save -> tests-data/fileapi/media/calendar/2/

        $fileutils = new FileUtils();

        $destfile = $this->API->getMediaDirPath() . $record . '/' . $filename;

        copy('tests-data/fileapi/save2/calendar/2/blackhole/' . $filename, $destfile);

        $destThumbnailDir = $this->API->getMediaDirPath() . $record . '/thumbnails';
        
        $fileutils->copydir('tests-data/fileapi/save2/calendar/2/blackhole/thumbnails', $destThumbnailDir);


        $this->assertTrue( file_exists( $destThumbnailDir ) && is_dir( $destThumbnailDir ) );

        // assert file exists before API call
        $this->assertTrue(file_exists($destfile));
        $this->path = $this->getApi() . '/fileapi/delete/calendar/2';

        $this->SERVER = ['REQUEST_URI' => $this->path, 'REQUEST_METHOD' => 'POST', 'HTTP_ORIGIN' => 'foobar'];

        $recordStr = '[{ "url": "'.$filename.'", "title":"test"}]';

        $this->POST = ['requestbody' => $recordStr];
        unset($recordStr);


        

        $response = $this->request('POST', $this->path);

        $this->printError($response);
        $this->assertEquals(200, $response->getCode());

        $this->assertTrue($response != null);

        // test deleted file
        $this->assertTrue(!file_exists($destfile));
    }

    public function testGet()
    {

          // echo 'testPostSuccess: ' . $this->memory();
        $this->path = $this->getApi() . '/fileapi/basicupload/calendar/1';
        $this->SERVER = ['REQUEST_URI' => $this->path, 'REQUEST_METHOD' => 'GET', 'HTTP_ORIGIN' => 'foobar'];
        $this->GET = ['requestbody' => '{}'];
        $recordStr = '[{ "url": "https://mit-license.org/index.html", "title":"MIT licence"}]';


        unset($recordStr);





  
        

        $response = $this->request('GET', $this->path);

        $this->printError($response);
        $this->assertEquals(200, $response->getCode());

        $this->assertTrue($response != null);
        $expected = '[{"title":"index.html","url":"index.html","size":2834,"mimetype":"text\/html"},{"title":"lorem ipsum.pdf","url":"lorem ipsum.pdf","size":24612,"mimetype":"application\/pdf"}]';

        $this->assertJsonStringEqualsJsonString($expected, $response->getEncodedResult());
    }

    public function testUploadFilePdf()
    {
        // API request
        $record = '/calendar/3';
        $this->path = $this->getApi() . '/fileapi/basicupload' . $record;
        $filename = 'testupload.pdf';
        // mock file
        $mockUploadedFile = realpath('tests-data/fileapi/save/') . '/' . 'upload_tmp123456789.pdf';
        copy('tests-data/fileapi/save/' . $filename, $mockUploadedFile);
        $files = [
        ['name'=>$filename,'type'=>'application/pdf','tmp_name'=> $mockUploadedFile,'error'=>0,'size'=>24612]
        ];

        $response = $this->fileRequest('POST', $this->path, $files);

        $this->printError($response);
        $this->assertEquals(200, $response->getCode());

        $this->assertTrue($response != null);
        $expected = '[{"title":"testupload.pdf","url":"testupload.pdf","size":24612,"mimetype":"application\/pdf"}]';

        $this->assertJsonStringEqualsJsonString($expected, $response->getEncodedResult());

        $mediaFile = $this->API->getMediaDirPath() . $record . '/' . $filename;
        $this->assertTrue(file_exists($mediaFile));
        unlink($mediaFile);
    }

    public function testUploadFileJpg()
    {
        // API request
        $record = '/calendar/3';
        $this->path = $this->getApi() . '/fileapi/basicupload' . $record;
        $filename = 'testupload.jpg';
        // mock file
        $mockUploadedFile = realpath('tests-data/fileapi/save/') . '/' . 'upload_tmp123456.jpg';
        copy('tests-data/fileapi/save/' . $filename, $mockUploadedFile);
        $files = [
        ['name'=>$filename,'type'=>'image/jpeg','tmp_name'=> $mockUploadedFile,'error'=>0,'size'=>146955]
        ];

        $response = $this->fileRequest('POST', $this->path, $files);

        $this->printError($response);
        $this->assertEquals(200, $response->getCode());

        $this->assertTrue($response != null);
        $expected = '[{"mimetype":"image\/jpeg","width":"640","height":"476","url":"testupload.jpg","size":146955,"title":"testupload.jpg"}]';

        $this->assertJsonStringEqualsJsonString($expected, $response->getEncodedResult());

        $mediaFile = $this->API->getMediaDirPath() . $record . '/' . $filename;
        $this->assertTrue(file_exists($mediaFile));
        unlink($mediaFile);
    }

    public function testUploadFileDoesNotExist()
    {
        // API request
        $record = '/calendar/3';
        $this->path = $this->getApi() . '/fileapi/basicupload' . $record;
        $filename = 'wrongfile.pdf';
        // mock file
        $mockUploadedFile = realpath('tests-data/fileapi/save/') . '/' . $filename;
        $files = [
        ['name'=>$filename,'type'=>'application/pdf','tmp_name'=> $mockUploadedFile,'error'=>0,'size'=>24612]
        ];

        // mock HTTP parameters
        $this->expectException(\Slim\Exception\HttpInternalServerErrorException::class);
        $response = $this->request('POST', $this->path, $files);

        $this->assertEquals(500, $response->getCode());

        $this->assertTrue($response != null);
        $expected = '{"error":"Uploaded file not found ' . $mockUploadedFile . '"}';

        $this->assertJsonStringEqualsJsonString($expected, $response->getEncodedResult());
    }


    public function testUploadFileForbiddenExtension()
    {
        // API request
        $record = '/calendar/3';
        $this->path = $this->getApi() . '/fileapi/basicupload' . $record;
        $filename = 'testupload.bmp';
        // mock file
        $mockUploadedFile = realpath('tests-data/fileapi/save/') . '/' . $filename;
        $files = [
            ['name'=>$filename,'type'=>'image/bmp','tmp_name'=> $mockUploadedFile,'error'=>0,'size'=>24612]
            ];

        $this->expectException(\Slim\Exception\HttpInternalServerErrorException::class);
        $response = $this->fileRequest('POST', $this->path, $files);
        $this->assertEquals(500, $response->getCode());

        $this->assertTrue($response != null);
        $expected = '{"error":"forbidden file type"}';

        $this->assertJsonStringEqualsJsonString($expected, $response->getEncodedResult());
    }
    public function testThumbnailsByRecord()
    {
        $record = '/clubs/1';
        $this->path = $this->getApi() . '/fileapi/thumbnails/clubs/1';

        $recordStr = '[{ "url": "tennisclub.jpg"}]';
        $this->POST = ['requestbody' => $recordStr];
        unset($recordStr);


        $response = $this->request('POST', $this->path);


        $this->assertEquals(200, $response->getCode());

        $this->assertTrue($response != null);

        $expected = '[{"mimetype":"image\/jpeg","width":"1200","height":"630","url":"tennisclub.jpg","thumbnails":[{"width":"32","height":"17","url":"tennisclub-32.jpg"},{"width":"48","height":"25","url":"tennisclub-48.jpg"},{"width":"64","height":"34","url":"tennisclub-64.jpg"},{"width":"128","height":"67","url":"tennisclub-128.jpg"},{"width":"256","height":"134","url":"tennisclub-256.jpg"}]}]';

        $this->assertJsonStringEqualsJsonString($expected, $response->getEncodedResult());

        $this->assertTrue(file_exists($this->API->getMediaDirPath() . $record . '/thumbnails/tennisclub-32.jpg'));
        unlink($this->API->getMediaDirPath() . $record . '/thumbnails/tennisclub-32.jpg');

        $this->assertTrue(file_exists($this->API->getMediaDirPath() . $record . '/thumbnails/tennisclub-48.jpg'));
        unlink($this->API->getMediaDirPath() . $record . '/thumbnails/tennisclub-48.jpg');



        $this->assertTrue(file_exists($this->API->getMediaDirPath() . $record . '/thumbnails/tennisclub-64.jpg'));
        unlink($this->API->getMediaDirPath() . $record . '/thumbnails/tennisclub-64.jpg');

        $this->assertTrue(file_exists($this->API->getMediaDirPath() . $record . '/thumbnails/tennisclub-128.jpg'));
        unlink($this->API->getMediaDirPath() . $record . '/thumbnails/tennisclub-128.jpg');

        $this->assertTrue(file_exists($this->API->getMediaDirPath() . $record . '/thumbnails/tennisclub-256.jpg'));
        unlink($this->API->getMediaDirPath() . $record . '/thumbnails/tennisclub-256.jpg');
    }

    public function testDefaultThumbnails()
    {
        $record = '/calendar/2';
        $this->path = $this->getApi() . '/fileapi/thumbnails/calendar/2';

        $recordStr = '[{ "url": "tennis.jpg"}]';

        $this->POST = ['requestbody' => $recordStr];
        unset($recordStr);


        $response = $this->request('POST', $this->path);


        $this->assertEquals(200, $response->getCode());

        $this->assertTrue($response != null);

        $expected = '[{"mimetype":"image\/jpeg","width":"640","height":"476","url":"tennis.jpg","thumbnails":[{"width":"150","height":"112","url":"tennis-150.jpg"},{"width":"300","height":"223","url":"tennis-300.jpg"}]}]';

        $this->assertJsonStringEqualsJsonString($expected, $response->getEncodedResult());

        $this->assertTrue(file_exists($this->API->getMediaDirPath() . $record . '/thumbnails/tennis-150.jpg'));
        unlink($this->API->getMediaDirPath() . $record . '/thumbnails/tennis-150.jpg');

        $this->assertTrue(file_exists($this->API->getMediaDirPath() . $record . '/thumbnails/tennis-300.jpg'));
        unlink($this->API->getMediaDirPath() . $record . '/thumbnails/tennis-300.jpg');
    }

    public function refreshThumbnails(string $record)
    {
        
        $this->path = $this->getApi() . '/fileapi/thumbnails' . $record;

        $recordStr = '';

        $this->POST = ['requestbody' => $recordStr];
        unset($recordStr);


        $response = $this->request('POST', $this->path);


        $this->assertEquals(200, $response->getCode());

        $this->assertTrue($response != null);

        $expected = '[]';

        $this->assertJsonStringEqualsJsonString($expected, $response->getEncodedResult());

  
    }


    public function testPdfThumbnails()
    {
        $record = '/calendar/2';
        $this->path = $this->getApi() . '/fileapi/thumbnails/calendar/2';

        $recordStr = '[{ "url": "loremipsum.pdf"}]';

        $this->POST = ['requestbody' => $recordStr];
        unset($recordStr);


        $response = $this->request('POST', $this->path);


        $this->assertEquals(200, $response->getCode());

        $this->assertTrue($response != null);

        $expected = '[{"mimetype":"application\/pdf","url":"loremipsum.pdf","thumbnails":[{"width":"100","height":"142","url":"loremipsum-100.jpg"},{"width":"200","height":"283","url":"loremipsum-200.jpg"}]}]';

        $this->assertJsonStringEqualsJsonString($expected, $response->getEncodedResult());

        $this->assertTrue(file_exists($this->API->getMediaDirPath() . $record . '/thumbnails/loremipsum-100.jpg'));
        unlink($this->API->getMediaDirPath() . $record . '/thumbnails/loremipsum-100.jpg');
        $this->assertTrue(file_exists($this->API->getMediaDirPath() . $record . '/thumbnails/loremipsum-200.jpg'));
        unlink($this->API->getMediaDirPath() . $record . '/thumbnails/loremipsum-200.jpg');
    }

    public function testThumbnailsDefaultSizes()
    {
        $record = '/calendar/2';
        $this->path = $this->getApi() . '/fileapi/thumbnails/calendar/2';

        $recordStr = '[{ "url": "tennis.jpg"}]';

        $this->POST = ['requestbody' => $recordStr];
        unset($recordStr);

        $response = $this->request('POST', $this->path);

        $this->printError($response);
        $this->assertEquals(200, $response->getCode());

        $this->assertTrue($response != null);

        $expected = '[{"width":"640","height":"476","url":"tennis.jpg","mimetype":"image\/jpeg","thumbnails":[{"width":"150","height":"112","url":"tennis-150.jpg"},{"width":"300","height":"223","url":"tennis-300.jpg"}]}]';

        $this->assertJsonStringEqualsJsonString($expected, $response->getEncodedResult());

        $this->assertTrue(file_exists($this->API->getMediaDirPath() . $record . '/thumbnails/tennis-150.jpg'));
        unlink($this->API->getMediaDirPath() . $record . '/thumbnails/tennis-150.jpg');
        $this->assertTrue(file_exists($this->API->getMediaDirPath() . $record . '/thumbnails/tennis-300.jpg'));
        unlink($this->API->getMediaDirPath() . $record . '/thumbnails/tennis-300.jpg');
    }
}
