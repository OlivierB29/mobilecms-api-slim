<?php

declare(strict_types=1);
namespace Tests\Application\Actions\Security;

use App\Application\Actions\ActionError;
use App\Application\Actions\ActionPayload;
use App\Application\Handlers\HttpErrorHandler;

use DI\Container;
use Slim\Middleware\ErrorMiddleware;

use Tests\AuthApiTest;
use App\Infrastructure\Utils\Properties;

use App\Infrastructure\Utils\FileUtils;

final class CmsApiSecurityTest extends AuthApiTest
{
    protected $requestparams = '?timestamp=1599654646';

    protected function setUp(): void
    {
        parent::setUp();
        $this->setAdmin();
    }


    public function testPostXssHtml1()
    {

        
        $this->path = $this->getApi() . '/cmsapi/content/calendar';


        $recordStr = file_get_contents('tests-data/security/htmlxss1.json');
        $this->POST = ['requestbody' => $recordStr];
        unset($recordStr);
        $response = $this->request('POST', $this->path);


        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
        // echo 'processAPI: ' . $this->memory();
        $this->assertTrue($response->getResult() != null && $response->getResult() != '');

        //TODO assert result without HTML
    }

    public function testPostXssHtml2()
    {

        
        $this->path = $this->getApi() . '/cmsapi/content/calendar';


        $recordStr = file_get_contents('tests-data/security/htmlxss2.json');
        $this->POST = ['requestbody' => $recordStr];
        unset($recordStr);
        $response = $this->request('POST', $this->path);


        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
        // echo 'processAPI: ' . $this->memory();
        $this->assertTrue($response->getResult() != null && $response->getResult() != '');

        //TODO assert result without HTML
    }
    public function testPostXssHtml3()
    {

        
        $this->path = $this->getApi() . '/cmsapi/content/calendar';


        $recordStr = file_get_contents('tests-data/security/htmlxss3.json');
        $this->POST = ['requestbody' => $recordStr];
        unset($recordStr);
        $response = $this->request('POST', $this->path);


        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
        // echo 'processAPI: ' . $this->memory();
        $this->assertTrue($response->getResult() != null && $response->getResult() != '');

        //TODO assert result without HTML
    }
    public function testPostXssHtml4()
    {

// TODO
// Double Open Angle Brackets <iframe src=http://xss.rocks/scriptlet.html < , 

        
        $this->path = $this->getApi() . '/cmsapi/content/calendar';


        $recordStr = file_get_contents('tests-data/security/htmlxss4.json');
        $this->POST = ['requestbody' => $recordStr];
        unset($recordStr);
        $response = $this->request('POST', $this->path);


        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
        // echo 'processAPI: ' . $this->memory();
        $this->assertTrue($response->getResult() != null && $response->getResult() != '');

        //TODO assert result without HTML
    }
    public function testPostXssHtml5()
    {

        
        $this->path = $this->getApi() . '/cmsapi/content/calendar';


        $recordStr = file_get_contents('tests-data/security/htmlxss5.json');
        $this->POST = ['requestbody' => $recordStr];
        unset($recordStr);
        $response = $this->request('POST', $this->path);


        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
        // echo 'processAPI: ' . $this->memory();
        $this->assertTrue($response->getResult() != null && $response->getResult() != '');

        //TODO assert result without HTML
    } 
    
    public function testPostXssHtml6()
    {

        
        $this->path = $this->getApi() . '/cmsapi/content/calendar';


        $recordStr = file_get_contents('tests-data/security/htmlxss6.json');
        $this->POST = ['requestbody' => $recordStr];
        unset($recordStr);
        $response = $this->request('POST', $this->path);


        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
        // echo 'processAPI: ' . $this->memory();
        $this->assertTrue($response->getResult() != null && $response->getResult() != '');

        //TODO assert result without HTML
    } 
    
    public function testPostXssHtml7()
    {

        //
        // ,  Extraneous Open Brackets <<SCRIPT>alert(\"XSS\");//\\<</SCRIPT> 
        // Half Open HTML/JavaScript XSS Vector <IMG SRC=\"`('XSS')\"` 

        $this->path = $this->getApi() . '/cmsapi/content/calendar';


        $recordStr = file_get_contents('tests-data/security/htmlxss7.json');
        $this->POST = ['requestbody' => $recordStr];
        unset($recordStr);
        $response = $this->request('POST', $this->path);


        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
        // echo 'processAPI: ' . $this->memory();
        $this->assertTrue($response->getResult() != null && $response->getResult() != '');

        //TODO assert result without HTML
    }

}
