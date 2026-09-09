<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Auth;

use App\Infrastructure\Services\ThrottleService;
use Tests\AuthApiUtility;

final class AuthenticationApiTest extends AuthApiUtility
{
    private $throttle;

    protected function setUp(): void
    {
        parent::setUp();
        $this->throttle = new ThrottleService($this->getPrivateDirPath().'/users');

        if (\file_exists($this->throttle->getLoginHistoryFileName('test@example.com'))) {
            \unlink($this->throttle->getLoginHistoryFileName('test@example.com'));
        }
    $this->seteditor();
    }

    public function testOptions()
    {
        $this->path = $this->getApi().'/authapi/authenticate';
        $response = $this->request('OPTIONS', $this->path);

        $this->assertTrue($response != null);
        $this->assertJsonStringEqualsJsonString('{}', $response->getEncodedResult());
        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
    }

   

    public function testResetPasswordOptions()
    {
        $this->path = $this->getApi().'/authapi/resetpassword';
        $response = $this->request('OPTIONS', $this->path);

        $this->assertTrue($response != null);
        $this->assertJsonStringEqualsJsonString('{}', $response->getEncodedResult());
        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
    }

    public function testChangePasswordOptions()
    {
        $this->path = $this->getApi().'/authapi/changepassword';
        $response = $this->request('OPTIONS', $this->path);

        $this->assertTrue($response != null);
        $this->assertJsonStringEqualsJsonString('{}', $response->getEncodedResult());
        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
    }

    public function testNoBody()
    {
        $this->path = $this->getApi().'/authapi/authenticate';

        $response = $this->request('POST', $this->path);

        $this->assertEquals(401, $response->getCode());
        $this->assertTrue($response != null);
    }



    public function testAuthByEmail()
    {
        $this->path = $this->getApi().'/authapi/authenticate';
        $recordStr = '{ "user": "editor@example.com", "password":"Sample#123456"}';

        $this->POST = ['requestbody' => $recordStr];

        $response = $this->request('POST', $this->path);

        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
        $this->assertTrue($response != null);

        $userObject  = $response->getResult();

        $this->assertTrue($userObject->{'email'} === 'editor@example.com');
        $this->assertTrue(strlen($userObject->{'token'}) > 150);
    }

    public function testNoPassword()
    {
        $this->path = $this->getApi().'/authapi/authenticate';
        $recordStr = '{ "user": "test@example.com"}';

        $this->POST = ['requestbody' => $recordStr];

        $response = $this->request('POST', $this->path);

        $this->assertEquals(401, $response->getCode());
    }

    public function testEmptyPassword()
    {
        $this->path = $this->getApi().'/authapi/authenticate';
        $recordStr = '{ "user": "test@example.com", "password":""}';

        $this->POST = ['requestbody' => $recordStr];

        $response = $this->request('POST', $this->path);

        $this->assertEquals(401, $response->getCode());
    }

    public function testEmptyUser()
    {
        $this->path = $this->getApi().'/authapi/authenticate';
        $recordStr = '{ "user": "","password":"foo"}';

        $this->POST = ['requestbody' => $recordStr];

        $response = $this->request('POST', $this->path);

        $this->assertEquals(401, $response->getCode());
    }

   

   
    public function testResetPassword()
    {
        $this->path = $this->getApi().'/authapi/resetpassword';
        $user = 'resetpassword@example.com';
        $userFile = $user.'.json';

        copy($this->getPrivateDirPath().'/save/'.$userFile, $this->getPrivateDirPath().'/users/'.$userFile);

        $recordStr = '{ "user": "'.$user.'", "password":"Sample#123456", "newpassword":"Foobar!654321"}';

        $this->POST = ['requestbody' => $recordStr];

        $response = $this->request('POST', $this->path);

        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
        $this->assertTrue($response != null);

        $userObject = $response->getResult();

        $this->assertTrue($userObject->{'name'} === $user);
        $this->assertTrue($userObject->{'clientalgorithm'} === 'none');
        $this->assertTrue($userObject->{'newpasswordrequired'} === 'true');

        $this->assertStringContainsString('password', $userObject->{'notification'});
        $this->assertStringContainsString('connection info', $userObject->{'notification'});
        $this->assertStringContainsString('date', $userObject->{'notification'});

        // delete file
        unlink($this->getPrivateDirPath().'/users/'.$userFile);
    }

    public function testChangePassword()
    {
        $this->path = $this->getApi().'/authapi/changepassword';
        $user = 'changepassword@example.com';
        $userFile = $user.'.json';

        copy($this->getPrivateDirPath().'/save/'.$userFile, $this->getPrivateDirPath().'/users/'.$userFile);

        $recordStr = '{ "user": "'.$user.'", "password":"Sample#123456", "newpassword":"Foobar!654321"}';

        $this->POST = ['requestbody' => $recordStr];
        $response = $this->request('POST', $this->path);

        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
        $this->assertTrue($response != null);

        // test new password with login
        $loginRecordStr = '{ "email": "'.$user.'", "password":"Foobar!654321"}';

        $recordStr = '{ "user": "changepassword@example.com", "password":"Foobar!654321"}';

        $this->verifyChangePassword($user, $recordStr);

        // delete file
        unlink($this->getPrivateDirPath().'/users/'.$userFile);
    }

    public function verifyChangePassword($user, $recordStr)
    {
        $this->path = $this->getApi().'/authapi/authenticate';

        $this->POST = ['requestbody' => $recordStr];
        $response = $this->request('POST', $this->path);

        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
        $this->assertTrue($response != null);

        $userObject = $response->getResult();

        $this->assertTrue($userObject->{'email'} === $user);
        $this->assertTrue(strlen($userObject->{'token'}) > 150);
    }
/*
    public function testPublicInfoGet()
    {
        $this->path = $this->getApi().'/authapi/publicinfo/editor@example.com';

        $response = $this->request('GET', $this->path);

        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
        $this->assertTrue($response != null);

        $userObject = $response->getResult();

        $this->assertTrue($userObject->{'name'} === 'editor@example.com');
        $this->assertTrue($userObject->{'clientalgorithm'} === 'none');
        $this->assertTrue($userObject->{'newpasswordrequired'} === 'false');
    }
*/
   

    public function testEmptyBody()
    {
        $this->path = $this->getApi().'/authapi/authenticate';
        $recordStr = '{ "user": "test@example.com", "password":"Sample#123456"}';

        $response = $this->request('POST', $this->path);

        $this->POST = ['requestbody' => ''];

        $this->assertEquals(401, $response->getCode());
        $this->assertTrue($response != null);
    }
}
