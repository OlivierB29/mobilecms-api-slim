<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Services;

use App\Infrastructure\Services\AuthService;
use App\Infrastructure\Services\ThrottleService;
use App\Infrastructure\Services\UserService;
use App\Infrastructure\Utils\NetUtils;
use App\Infrastructure\Utils\JsonUtils;
use Tests\ApiUtility;

final class AuthServiceTest extends ApiUtility
{
    private $service;
    private $throttle;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AuthService('tests-data/userservice');
        $this->throttle = new ThrottleService('tests-data/userservice');

        if (\file_exists($this->throttle->getLoginHistoryFileName('test@example.com'))) {
            \unlink($this->throttle->getLoginHistoryFileName('test@example.com'));
        }
    }

    public function testServiceCreateUser()
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

    public function testServiceCreateUserEmptyEmail()
    {
        $mail = '';
        $password = 'foo';
        $createresult = $this->service->createUser($mail, $mail, $password, 'create');
        $this->assertTrue($createresult !== null);
        $this->assertTrue(strpos($createresult, 'EmptyEmail') !== false);
    }

    public function testServiceCreateUserInvalidEmail()
    {
        $mail = 'foobar';
        $password = 'foo';
        $createresult = $this->service->createUser($mail, $mail, $password, 'create');
        $this->assertTrue($createresult !== null);
        $this->assertTrue(strpos($createresult, 'InvalidEmail') !== false);
    }

    public function testServiceCreateUserAlreadyExists()
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

    public function testServiceCreateUserEmptyPassword()
    {
        $mail = 'testcreate@example.com';
        $password = '';
        $createresult = $this->service->createUser($mail, $mail, $password, 'create');
        $this->assertTrue($createresult !== null);
        $this->assertTrue(strpos($createresult, 'EmptyPassword') !== false);
    }

    public function testServiceCreateUserEmptyUsername()
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

        $result = $this->service->login('test@example.com', 'Sample#123456', null);

        $this->assertTrue('' === $result);
    }

    public function testServiceGetToken()
    {
        if (\file_exists($this->throttle->getLoginHistoryFileName('test@example.com'))) {
            \unlink($this->throttle->getLoginHistoryFileName('test@example.com'));
        }

        $result = $this->service->getToken('test@example.com', 'Sample#123456');
        $this->assertTrue($result->getCode() === 200);
        $this->assertTrue(null != $result->getResult());

        $user = $result->getResult();

        $this->assertTrue($user->{'name'} === 'test@example.com');
        $this->assertTrue($user->{'email'} === 'test@example.com');
        $this->assertTrue(strlen($user->{'token'}) > 100);
    }

    public function testRateLimitedTokenIncludesRetryAfter()
    {
        $email = 'test@example.com';
        $ip = NetUtils::getClientIp();
        $this->throttle->clearFailedLogins($email, $ip);

        try {
            for ($attempt = 0; $attempt < 20; ++$attempt) {
                $this->throttle->recordFailedLogin($email, $ip);
            }

            $result = $this->service->getToken($email, 'Sample#123456');

            $this->assertSame(429, $result->getCode());
            $this->assertGreaterThan(0, $result->getResult()->{'retryAfter'});
        } finally {
            $this->throttle->clearFailedLogins($email, $ip);
        }
    }

    public function testServiceEmptyToken()
    {
        $this->expectException(\Exception::class);
        $result = $this->service->verifyToken(null, 'editor');
    }

    public function testServiceVerifyToken()
    {
        $getTokenResponse = $this->service->getToken('test@example.com', 'Sample#123456');

        $user = $getTokenResponse->getResult();

        $result = $this->service->verifyToken($user->{'token'}, 'editor');

        $this->assertEquals(200, $result->getCode());
    }

    public function testServiceVerifyWrongToken()
    {
        $getTokenResponse = $this->service->getToken('test@example.com', 'Sample#123456');

        $user = $getTokenResponse->getResult();

        $result = $this->service->verifyToken($user->{'token'}.'FOOBAR', 'editor');

        $this->assertTrue($result->getCode() === 401);
    }

    public function testServiceInsufficentEditorRole()
    {
        $getTokenResponse = $this->service->getToken('guest@example.com', 'Sample#123456');

        $user = $getTokenResponse->getResult();

        $result = $this->service->verifyToken($user->{'token'}, 'editor');

        $this->assertEquals(403, $result->getCode());
    }

    public function testServiceInsufficentAdminRole()
    {
        $getTokenResponse = $this->service->getToken('test@example.com', 'Sample#123456');

        $user = $getTokenResponse->getResult();

        $result = $this->service->verifyToken($user->{'token'}, 'admin');

        $this->assertEquals(403, $result->getCode());
    }

    public function testServiceVerifyEditorByAdmin()
    {
        $getTokenResponse = $this->service->getToken('admin@example.com', 'Sample#123456');

        $user = $getTokenResponse->getResult();

        $result = $this->service->verifyToken($user->{'token'}, 'editor');

        $this->assertEquals(200, $result->getCode());
    }

    public function testServiceVerifyAdmin()
    {
        $getTokenResponse = $this->service->getToken('admin@example.com', 'Sample#123456');

        $user = $getTokenResponse->getResult();

        $result = $this->service->verifyToken($user->{'token'}, 'admin');

        $this->assertEquals(200, $result->getCode());
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

    public function testPasswordResetRequiresValidSingleUseTokenAndDoesNotChangePasswordOnRequest()
    {
        $email = 'passwordreset@example.com';
        $userService = new UserService('tests-data/userservice');
        $file = $userService->getJsonUserFile($email);
        $this->service->createUser($email, $email, 'old-password', 'create');

        try {
            $before = $userService->getJsonUser($email);
            $token = $this->service->requestPasswordReset($email);

            $this->assertNotNull($token);
            $this->assertSame(64, strlen($token));
            $pending = $userService->getJsonUser($email);
            $this->assertSame($before->{'password'}, $pending->{'password'});
            $this->assertSame($before->{'salt'}, $pending->{'salt'});
            $this->assertNotSame($token, $pending->{'temporarypassword'});
            $this->assertGreaterThan(time(), $pending->{'temporaryvalidity'});
            $this->assertNull($this->service->requestPasswordReset($email));
            $this->assertSame(200, $this->service->validateTemporaryLogin($email, $token)->getCode());

            $completed = $this->service->completePasswordReset($email, $token, 'new-password');
            $this->assertSame(200, $completed->getCode());
            $updated = $userService->getJsonUser($email);
            $this->assertNotSame($before->{'salt'}, $updated->{'salt'});
            $this->assertSame('', $updated->{'temporarypassword'});
            $this->assertSame(0, $updated->{'temporaryvalidity'});
            $this->assertSame(401, $this->service->validateTemporaryLogin($email, $token)->getCode());
            $this->assertSame(401, $this->service->getToken($email, 'old-password')->getCode());
            $this->assertSame(200, $this->service->getToken($email, 'new-password')->getCode());
        } finally {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    public function testPasswordResetRejectsExpiredToken()
    {
        $email = 'expiredreset@example.com';
        $userService = new UserService('tests-data/userservice');
        $file = $userService->getJsonUserFile($email);
        $this->service->createUser($email, $email, 'old-password', 'create');

        try {
            $token = $this->service->requestPasswordReset($email);
            $user = $userService->getJsonUser($email);
            $user->{'temporaryvalidity'} = time() - 1;
            JsonUtils::writeJsonFile($file, $user);

            $this->assertSame(401, $this->service->validateTemporaryLogin($email, $token)->getCode());
            $this->assertSame(401, $this->service->completePasswordReset($email, $token, 'new-password')->getCode());
        } finally {
            if (file_exists($file)) {
                unlink($file);
            }
        }
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
