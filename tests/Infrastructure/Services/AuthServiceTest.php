<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Services;

use App\Infrastructure\Services\AuthService;
use App\Infrastructure\Services\ThrottleService;
use PHPUnit\Framework\TestCase;

final class AuthServiceTest extends TestCase
{
    private $service;
    private $throttle;

    protected function setUp(): void
    {
        $this->service = new AuthService('tests-data/userservice');
        $this->throttle = new ThrottleService('tests-data/userservice');

        if (\file_exists($this->throttle->getLoginHistoryFileName('test@example.com'))) {
            \unlink($this->throttle->getLoginHistoryFileName('test@example.com'));
        }
        if (\file_exists($this->throttle->getCaptchaFileName('test@example.com'))) {
            \unlink($this->throttle->getCaptchaFileName('test@example.com'));
        }
    }

    public function testCreateUser()
    {
        $mail = 'testcreate@example.com';
        $file = 'tests-data/userservice/'.$mail.'.json';
        if (file_exists($file)) {
            unlink($file);
        }
        $password = 'Sample#123456';

        $createresult = $this->service->createUser($mail, $mail, $password, 'create');
        $this->assertTrue($createresult === null);
        unlink($file);
    }

    public function testCreateUserEmptyEmail()
    {
        $mail = '';
        $password = 'foo';
        $createresult = $this->service->createUser($mail, $mail, $password, 'create');
        $this->assertTrue($createresult !== null);
        $this->assertTrue(strpos($createresult, 'EmptyEmail') !== false);
    }

    public function testCreateUserInvalidEmail()
    {
        $mail = 'foobar';
        $password = 'foo';
        $createresult = $this->service->createUser($mail, $mail, $password, 'create');
        $this->assertTrue($createresult !== null);
        $this->assertTrue(strpos($createresult, 'InvalidEmail') !== false);
    }

    public function testCreateUserAlreadyExists()
    {
        $mail = 'testcreate@example.com';
        $file = 'tests-data/userservice/'.$mail.'.json';
        if (file_exists($file)) {
            unlink($file);
        }
        $password = 'Sample#123456';

        $this->service->createUser($mail, $mail, $password, 'create');
        $createresult = $this->service->createUser($mail, $mail, $password, 'create');
        $this->assertTrue($createresult !== null);
        unlink($file);
    }

    public function testCreateUserEmptyPassword()
    {
        $mail = 'testcreate@example.com';
        $password = '';
        $createresult = $this->service->createUser($mail, $mail, $password, 'create');
        $this->assertTrue($createresult !== null);
        $this->assertTrue(strpos($createresult, 'EmptyPassword') !== false);
    }

    public function testCreateUserEmptyUsername()
    {
        $mail = 'testcreate@example.com';
        $password = 'foo';
        $createresult = $this->service->createUser('', $mail, $password, 'create');
        $this->assertTrue($createresult !== null);
        $this->assertTrue(strpos($createresult, 'InvalidUser') !== false);
    }

    public function testLoginOk()
    {
        if (\file_exists($this->throttle->getLoginHistoryFileName('test@example.com'))) {
            \unlink($this->throttle->getLoginHistoryFileName('test@example.com'));
        }

        if (\file_exists($this->throttle->getCaptchaFileName('test@example.com'))) {
            \unlink($this->throttle->getCaptchaFileName('test@example.com'));
        }
        $result = $this->service->login('test@example.com', 'Sample#123456', null);

        $this->assertTrue('' === $result);
    }

    public function testGetToken()
    {
        if (\file_exists($this->throttle->getLoginHistoryFileName('test@example.com'))) {
            \unlink($this->throttle->getLoginHistoryFileName('test@example.com'));
        }
        if (\file_exists($this->throttle->getCaptchaFileName('test@example.com'))) {
            \unlink($this->throttle->getCaptchaFileName('test@example.com'));
        }

        $result = $this->service->getToken('test@example.com', 'Sample#123456');
        $this->assertTrue($result->getCode() === 200);
        $this->assertTrue(null != $result->getResult());

        $user = $result->getResult();

        $this->assertTrue($user->{'name'} === 'test@example.com');
        $this->assertTrue($user->{'email'} === 'test@example.com');
        $this->assertTrue(strlen($user->{'token'}) > 100);
    }

    public function testEmptyToken()
    {
        $this->expectException(\Exception::class);
        $result = $this->service->verifyToken(null, 'editor');
    }

    public function testVerifyToken()
    {
        $getTokenResponse = $this->service->getToken('test@example.com', 'Sample#123456');

        $user = $getTokenResponse->getResult();

        $result = $this->service->verifyToken($user->{'token'}, 'editor');

        $this->assertTrue($result->getCode() === 200);
    }

    public function testVerifyWrongToken()
    {
        $getTokenResponse = $this->service->getToken('test@example.com', 'Sample#123456');

        $user = $getTokenResponse->getResult();

        $result = $this->service->verifyToken($user->{'token'}.'FOOBAR', 'editor');

        $this->assertTrue($result->getCode() === 401);
    }

    public function testInsufficentEditorRole()
    {
        $getTokenResponse = $this->service->getToken('guest@example.com', 'Sample#123456');

        $user = $getTokenResponse->getResult();

        $result = $this->service->verifyToken($user->{'token'}, 'editor');

        $this->assertTrue($result->getCode() === 403);
    }

    public function testInsufficentAdminRole()
    {
        $getTokenResponse = $this->service->getToken('test@example.com', 'Sample#123456');

        $user = $getTokenResponse->getResult();

        $result = $this->service->verifyToken($user->{'token'}, 'admin');

        $this->assertTrue($result->getCode() === 403);
    }

    public function testVerifyEditorByAdmin()
    {
        $getTokenResponse = $this->service->getToken('admin@example.com', 'Sample#123456');

        $user = $getTokenResponse->getResult();

        $result = $this->service->verifyToken($user->{'token'}, 'editor');

        $this->assertTrue($result->getCode() === 200);
    }

    public function testVerifyAdmin()
    {
        $getTokenResponse = $this->service->getToken('admin@example.com', 'Sample#123456');

        $user = $getTokenResponse->getResult();

        $result = $this->service->verifyToken($user->{'token'}, 'admin');

        $this->assertTrue($result->getCode() === 200);
    }

    public function testTokenKo()
    {
        $result = $this->service->getToken('test@example.com', 'Sample#1234567');
        $this->assertTrue($result->getCode() === 401);
        $this->assertTrue($result->getEncodedResult() !== '');
    }

    public function testWrongLogin1()
    {
        $result = $this->service->login('test@example.com', 'wrongpass', null);
        $this->assertTrue($result !== null);
    }

    public function testWrongLogin2()
    {
        $result = $this->service->login('test@example.com', 'Sample#12345', null);
        $this->assertTrue($result !== null);
    }

    public function testResetPasswordWrongUser()
    {
        $this->expectException(\Exception::class);
        $this->service->resetPassword('FOOBAR@example.com', 'foo');
    }

    public function testModifyPassword()
    {
        $userdir = 'tests-data/userservice';
        $email = 'modifypasssword@example.com';

        copy($userdir.'/'.$email.'.backup.json', $userdir.'/'.$email.'.json');

        $oldPassword = 'Sample#123456';

        $service = new AuthService($userdir);

        //change password
        $newPassword = 'somethingnew';
        $createresult = $this->service->changePassword($email, $oldPassword, $newPassword, null);

        $this->assertTrue($createresult->getCode() === 200);

        //login
        $result = $this->service->login($email, $newPassword, null);
        $this->assertTrue('' === $result);
    }

    public function testModifyWithWrongPassword()
    {
        $userdir = 'tests-data/userservice';
        $email = 'modifypasssword@example.com';

        copy($userdir.'/'.$email.'.backup.json', $userdir.'/'.$email.'.json');

        $oldPassword = 'foo';

        $service = new AuthService($userdir);

        //change password
        $newPassword = 'somethingnew';
        $createresult = $this->service->changePassword($email, $oldPassword, $newPassword, null);

        $this->assertTrue($createresult->getCode() === 401);
    }
}
