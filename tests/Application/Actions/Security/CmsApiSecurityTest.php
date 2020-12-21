<?php

declare(strict_types=1);
namespace Tests\Application\Actions\Cms;

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


    public function testPostHtml()
    {

        // echo 'testPostSuccess: ' . $this->memory();
        $this->path = $this->getApi() . '/cmsapi/content/calendar';


        $recordStr = file_get_contents('tests-data/security/htmlxss.json');
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
