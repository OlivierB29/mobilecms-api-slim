<?php

declare(strict_types=1);
namespace Tests\Application\Actions\Auth;

use App\Application\Actions\ActionError;
use App\Application\Actions\ActionPayload;
use App\Application\Handlers\HttpErrorHandler;

use DI\Container;
use Slim\Middleware\ErrorMiddleware;

use Tests\ApiTest;
use App\Infrastructure\Utils\Properties;

use App\Infrastructure\Utils\FileUtils;

final class AuthenticationApiTest extends ApiTest
{
    protected function setUp(): void
    {
        parent::setUp();
    }




    public function testOptions()
    {
        $this->path = $this->getApi() . '/authapi/authenticate';
        $response = $this->request('OPTIONS', $this->path);






        $this->assertTrue($response != null);
        $this->assertJsonStringEqualsJsonString('{}', $response->getEncodedResult());
        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
    }

    public function testRegisterOptions()
    {
        $this->path = $this->getApi() . '/authapi/register';
        $response = $this->request('OPTIONS', $this->path);






        $this->assertTrue($response != null);
        $this->assertJsonStringEqualsJsonString('{}', $response->getEncodedResult());
        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
    }

    public function testResetPasswordOptions()
    {
        $this->path = $this->getApi() . '/authapi/resetpassword';
        $response = $this->request('OPTIONS', $this->path);






        $this->assertTrue($response != null);
        $this->assertJsonStringEqualsJsonString('{}', $response->getEncodedResult());
        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
    }
    public function testChangePasswordOptions()
    {
        $this->path = $this->getApi() . '/authapi/changepassword';
        $response = $this->request('OPTIONS', $this->path);






        $this->assertTrue($response != null);
        $this->assertJsonStringEqualsJsonString('{}', $response->getEncodedResult());
        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
    }

    public function testNoBody()
    {
        $this->path = $this->getApi() . '/authapi/authenticate';
        
        
        
        $response = $this->request('POST', $this->path);




        $this->assertEquals(401, $response->getCode());
        $this->assertTrue($response != null);
    }

   

    public function testAuthByUser()
    {
        $this->path = $this->getApi() . '/authapi/authenticate';
        $recordStr = '{ "user": "test@example.com", "password":"Sample#123456"}';

        



        $this->POST = ['requestbody' => $recordStr];
        $response = $this->request('POST', $this->path);


        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
        $this->assertTrue($response != null);

        $userObject = $response->getResult();

        $this->assertTrue($userObject->{'email'} === 'test@example.com');
        $this->assertTrue(strlen($userObject->{'token'}) > 150);
    }

    public function testAuthByEmail()
    {
        $this->path = $this->getApi() . '/authapi/authenticate';
        $recordStr = '{ "email": "test@example.com", "password":"Sample#123456"}';

        


        $this->POST = ['requestbody' => $recordStr];

        $response = $this->request('POST', $this->path);


        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
        $this->assertTrue($response != null);

        $userObject = $response->getResult();

        $this->assertTrue($userObject->{'email'} === 'test@example.com');
        $this->assertTrue(strlen($userObject->{'token'}) > 150);
    }

    public function testNoPassword()
    {
        $this->path = $this->getApi() . '/authapi/authenticate';
        $recordStr = '{ "user": "test@example.com"}';

        



        $this->POST = ['requestbody' => $recordStr];
        
        $response = $this->request('POST', $this->path);


        $this->assertEquals(401, $response->getCode());
    }

    public function testEmptyPassword()
    {
        $this->path = $this->getApi() . '/authapi/authenticate';
        $recordStr = '{ "user": "test@example.com", "password":""}';

        



        $this->POST = ['requestbody' => $recordStr];
        
        $response = $this->request('POST', $this->path);


        $this->assertEquals(401, $response->getCode());
    }

    public function testEmptyUser()
    {
        $this->path = $this->getApi() . '/authapi/authenticate';
        $recordStr = '{ "user": "","password":"foo"}';

        



        $this->POST = ['requestbody' => $recordStr];


        $response = $this->request('POST', $this->path);


        $this->assertEquals(401, $response->getCode());
    }

    public function testRegister()
    {
        $this->path = $this->getApi() . '/authapi/register';

        $email = 'testregister@example.com';

        $file = $this->getPrivateDirPath() . '/users/' . $email . '.json';
        if (file_exists($file)) {
            unlink($file);
        }



        $recordStr = '{ "name": "test register", "email": "testregister@example.com", "password":"Sample#123456", "secretQuestion": "some secret" , "secretResponse": "secret response"}';

        



        $this->POST = ['requestbody' => $recordStr];

        $response = $this->request('POST', $this->path);


        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
        $this->assertTrue($response != null);


        if (file_exists($file)) {
            unlink($file);
        }
    }

    public function testRegisterEmptyParam()
    {
        $this->path = $this->getApi() . '/authapi/register';

        $email = 'testregister@example.com';

        $file = $this->getPrivateDirPath() . '/users/' . $email . '.json';
        if (file_exists($file)) {
            unlink($file);
        }



        $recordStr = '{ "name": "test register", "email": "", "password":""}';

        


        $this->POST = ['requestbody' => $recordStr];
        
        $response = $this->request('POST', $this->path);



        $this->assertEquals(400, $response->getCode());
        $this->assertTrue($response != null);
    }

    public function testResetPassword()
    {
        $this->path = $this->getApi() . '/authapi/resetpassword';
        $user = 'resetpassword@example.com';
        $userFile = $user . '.json';

        copy($this->getPrivateDirPath() . '/save/' . $userFile, $this->getPrivateDirPath() . '/users/' . $userFile);

        $recordStr = '{ "user": "' . $user . '", "password":"Sample#123456", "newpassword":"Foobar!654321"}';

        



        $this->POST = ['requestbody' => $recordStr];

        $response = $this->request('POST', $this->path);



        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
        $this->assertTrue($response != null);

        $userObject = $response->getResult();

        $this->assertTrue($userObject->{'name'} === $user);
        $this->assertTrue($userObject->{'clientalgorithm'} === 'none');
        $this->assertTrue($userObject->{'newpasswordrequired'} === 'true');
 

        $this->assertStringContainsString('DOCTYPE', $userObject->{'notification'});
        $this->assertStringContainsString('meta charset', $userObject->{'notification'});
        $this->assertStringContainsString('Password', $userObject->{'notification'});
        $this->assertStringContainsString('Connection info', $userObject->{'notification'});

        // delete file
        unlink($this->getPrivateDirPath() . '/users/' . $userFile);
    }

    public function testChangePassword()
    {
        $this->path = $this->getApi() . '/authapi/changepassword';
        $user = 'changepassword@example.com';
        $userFile = $user . '.json';

        copy($this->getPrivateDirPath() . '/save/' . $userFile, $this->getPrivateDirPath() . '/users/' . $userFile);

        $recordStr = '{ "user": "' . $user . '", "password":"Sample#123456", "newpassword":"Foobar!654321"}';

        

        $this->POST = ['requestbody' => $recordStr];
        $response = $this->request('POST', $this->path);

        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
        $this->assertTrue($response != null);

        // test new password with login
        $loginRecordStr = '{ "email": "' . $user . '", "password":"Foobar!654321"}';

        $recordStr = '{ "user": "changepassword@example.com", "password":"Foobar!654321"}';

        $this->verifyChangePassword($user, $recordStr);

        // delete file
        unlink($this->getPrivateDirPath() . '/users/' . $userFile);
    }

    public function verifyChangePassword($user, $recordStr)
    {
        $this->path = $this->getApi() . '/authapi/authenticate';

        
        $this->POST = ['requestbody' => $recordStr];
        $response = $this->request('POST', $this->path);

        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
        $this->assertTrue($response != null);

        $userObject = $response->getResult();

        $this->assertTrue($userObject->{'email'} === $user);
        $this->assertTrue(strlen($userObject->{'token'}) > 150);
    }

    public function testPublicInfoGet()
    {
        $this->path = $this->getApi() . '/authapi/publicinfo/editor@example.com';

        

        $response = $this->request('GET', $this->path);





        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
        $this->assertTrue($response != null);

        $userObject = $response->getResult();

        $this->assertTrue($userObject->{'name'} === 'editor@example.com');
        $this->assertTrue($userObject->{'clientalgorithm'} === 'none');
        $this->assertTrue($userObject->{'newpasswordrequired'} === 'false');
    }

    public function testPublicInfoPost()
    {
        $this->path = $this->getApi() . '/authapi/publicinfo';

        


        $recordStr = '{ "email": "editor@example.com" }';
        $this->POST = ['requestbody' => $recordStr];
        $response = $this->request('POST', $this->path);



        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
        $this->assertTrue($response != null);

        $userObject = $response->getResult();

        $this->assertTrue($userObject->{'name'} === 'editor@example.com');
        $this->assertTrue($userObject->{'clientalgorithm'} === 'none');
        $this->assertTrue($userObject->{'newpasswordrequired'} === 'false');
    }

    public function testEmptyBody()
    {
        $this->path = $this->getApi() . '/authapi/authenticate';
        $recordStr = '{ "user": "test@example.com", "password":"Sample#123456"}';

        
        
        $response = $this->request('POST', $this->path);

        $this->POST = ['requestbody' => ''];




        $this->assertEquals(401, $response->getCode());
        $this->assertTrue($response != null);
    }
}
