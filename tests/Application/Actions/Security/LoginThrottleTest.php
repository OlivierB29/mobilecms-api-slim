<?php

declare(strict_types=1);
namespace Tests\Application\Actions\Security;

use App\Application\Actions\ActionError;
use App\Application\Actions\ActionPayload;
use App\Application\Handlers\HttpErrorHandler;

use DI\Container;
use Slim\Middleware\ErrorMiddleware;

use Tests\ApiTest;
use App\Infrastructure\Utils\Properties;

use App\Infrastructure\Utils\FileUtils;
use App\Infrastructure\Services\ThrottleService;
use  App\Infrastructure\Utils\JsonUtils;

final class LoginThrottleTest extends ApiTest
{
    protected $requestparams = '?timestamp=1599654646';
    protected $throttle ;


    protected function setUp(): void
    {
        parent::setUp();
        $this->throttle = new ThrottleService($this->getPrivateDirPath() . '/users');

        if (\file_exists($this->throttle->getLoginHistoryFileName("captchatest@example.com"))) {
            \unlink($this->throttle->getLoginHistoryFileName("captchatest@example.com"));
        }
        if (\file_exists($this->throttle->getCaptchaFileName("captchatest@example.com"))) {
            \unlink($this->throttle->getCaptchaFileName("captchatest@example.com"));
        }
    }

    

    public function testCreateCaptcha()
    {
        $this->path = $this->getApi() . '/authapi/authenticate';

        $recordStr = '{ "user": "captchatest@example.com", "password":"wrong1"}';
        $this->POST = ['requestbody' => $recordStr];
        $response = $this->request('POST', $this->path);
        $this->assertEquals(401, $response->getCode());

        $recordStr = '{ "user": "captchatest@example.com", "password":"wrong2"}';
        $this->POST = ['requestbody' => $recordStr];
        $response = $this->request('POST', $this->path);
        $this->assertEquals(401, $response->getCode());

        $recordStr = '{ "user": "captchatest@example.com", "password":"wrong3"}';
        $this->POST = ['requestbody' => $recordStr];
        $response = $this->request('POST', $this->path);
        $this->assertEquals(401, $response->getCode());




        $this->path = $this->getApi() . '/authapi/publicinfo/captchatest@example.com';
        $response = $this->request('GET', $this->path);
        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
        $this->assertTrue($response != null);
        $userObject = $response->getResult();
        $this->assertTrue($userObject->{'name'} === 'captchatest@example.com');
        $this->assertTrue($userObject->{'clientalgorithm'} === 'none');
        $this->assertTrue($userObject->{'newpasswordrequired'} === 'false');
        
 

        $captachaFile = $this->throttle->getCaptchaFileName("captchatest@example.com");
        $captcha = JsonUtils::readJsonFile($captachaFile);

        $this->assertTrue($userObject->{'captcha'} === $captcha->{'question'});
    }



    public function testValidateCaptcha()
    {
        $this->path = $this->getApi() . '/authapi/authenticate';

        $recordStr = '{ "user": "captchatest@example.com", "password":"wrong1"}';
        $this->POST = ['requestbody' => $recordStr];
        $response = $this->request('POST', $this->path);
        $this->assertEquals(401, $response->getCode());

        $recordStr = '{ "user": "captchatest@example.com", "password":"wrong2"}';
        $this->POST = ['requestbody' => $recordStr];
        $response = $this->request('POST', $this->path);
        $this->assertEquals(401, $response->getCode());




        $this->path = $this->getApi() . '/authapi/publicinfo/captchatest@example.com';
        $response = $this->request('GET', $this->path);
        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
        $this->assertTrue($response != null);
        $userObject = $response->getResult();
        $this->assertTrue($userObject->{'name'} === 'captchatest@example.com');
        $this->assertTrue($userObject->{'clientalgorithm'} === 'none');
        $this->assertTrue($userObject->{'newpasswordrequired'} === 'false');
        
 

        $captachaFile = $this->throttle->getCaptchaFileName("captchatest@example.com");
        $captcha = JsonUtils::readJsonFile($captachaFile);

        $this->assertTrue($userObject->{'captcha'} === $captcha->{'question'});


        // Authenticate and control captcha
        $this->path = $this->getApi() . '/authapi/authenticate';
        $recordStr = '{ "email": "captchatest@example.com", "password":"Sample#123456", "captchaanswer":"' . $captcha->{'answer'} . '"}';

        


        $this->POST = ['requestbody' => $recordStr];

        $response = $this->request('POST', $this->path);


        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
        $this->assertTrue($response != null);

        $userObject = $response->getResult();

        $this->assertTrue($userObject->{'email'} === 'captchatest@example.com');
        $this->assertTrue(strlen($userObject->{'token'}) > 150);
    }
}
