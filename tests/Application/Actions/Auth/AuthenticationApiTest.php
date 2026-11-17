<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Auth;

use App\Infrastructure\Services\ThrottleService;
use App\Infrastructure\Services\AuthService;
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

        $userObject = $response->getResult();

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
        $ip = 'test-reset-success-'.bin2hex(random_bytes(8));
        $throttleFile = $this->getPrivateDirPath().'/users/history/reset-'.hash('sha256', $ip).'.json';
        $historyFile = $this->getPrivateDirPath().'/users/history/'.$user.'.json';
        $originalIp = $_SERVER['REMOTE_ADDR'] ?? null;
        $_SERVER['REMOTE_ADDR'] = $ip;
        $this->POST = ['requestbody' => '{"user":"'.$user.'"}'];

        try {
            $userBefore = json_decode(file_get_contents($this->getPrivateDirPath().'/users/'.$userFile));
            $response = $this->request('POST', $this->path);

            $this->printError($response);
            $this->assertEquals(200, $response->getCode());
            $this->assertSame('{}', $response->getEncodedResult());

            $pending = json_decode(file_get_contents($this->getPrivateDirPath().'/users/'.$userFile));
            $this->assertSame($userBefore->{'password'}, $pending->{'password'});
            $this->assertSame($userBefore->{'salt'}, $pending->{'salt'});
            $this->assertNotEmpty($pending->{'temporarypassword'});
            $this->assertGreaterThan(time(), $pending->{'temporaryvalidity'});
        } finally {
            if (file_exists($throttleFile)) {
                unlink($throttleFile);
            }
            if (file_exists($historyFile)) {
                unlink($historyFile);
            }
            if (file_exists($this->getPrivateDirPath().'/users/'.$userFile)) {
                unlink($this->getPrivateDirPath().'/users/'.$userFile);
            }
            if ($originalIp === null) {
                unset($_SERVER['REMOTE_ADDR']);
            } else {
                $_SERVER['REMOTE_ADDR'] = $originalIp;
            }
        }
    }

    public function testPasswordResetDoesNotRevealWhetherUserExists(): void
    {
        $this->path = $this->getApi().'/authapi/resetpassword';
        $user = 'resetpassword@example.com';
        $userFile = $this->getPrivateDirPath().'/users/'.$user.'.json';
        copy($this->getPrivateDirPath().'/save/'.$user.'.json', $userFile);
        $originalIp = $_SERVER['REMOTE_ADDR'] ?? null;

        try {
            $responses = [];
            foreach ([$user, 'unknown-reset-user@example.com'] as $email) {
                $_SERVER['REMOTE_ADDR'] = 'reset-enumeration-'.bin2hex(random_bytes(8));
                $this->POST = ['requestbody' => json_encode(['user' => $email])];
                $responses[] = $this->request('POST', $this->path);
            }

            $this->assertSame(200, $responses[0]->getCode());
            $this->assertSame(200, $responses[1]->getCode());
            $this->assertSame($responses[0]->getEncodedResult(), $responses[1]->getEncodedResult());
        } finally {
            if (file_exists($userFile)) {
                unlink($userFile);
            }
            if ($originalIp === null) {
                unset($_SERVER['REMOTE_ADDR']);
            } else {
                $_SERVER['REMOTE_ADDR'] = $originalIp;
            }
        }
    }

    public function testTemporaryLoginAndConfirmPasswordReset(): void
    {
        $user = 'resetpassword@example.com';
        $userFile = $user.'.json';
        $userPath = $this->getPrivateDirPath().'/users/'.$userFile;
        copy($this->getPrivateDirPath().'/save/'.$userFile, $userPath);
        $service = new AuthService($this->getPrivateDirPath().'/users');

        try {
            $before = json_decode(file_get_contents($userPath));
            $token = $service->requestPasswordReset($user);
            $pending = json_decode(file_get_contents($userPath));
            $this->assertSame($before->{'password'}, $pending->{'password'});
            $this->assertSame($before->{'salt'}, $pending->{'salt'});

            $this->path = $this->getApi().'/authapi/temporarylogin';
            $this->POST = ['requestbody' => json_encode(['user' => $user, 'token' => $token])];
            $this->assertSame(200, $this->request('POST', $this->path)->getCode());

            $this->path = $this->getApi().'/authapi/resetpassword/confirm';
            $this->POST = ['requestbody' => json_encode([
                'user' => $user,
                'token' => $token,
                'newpassword' => 'Foobar!654321',
            ])];
            $this->assertSame(200, $this->request('POST', $this->path)->getCode());

            $updated = json_decode(file_get_contents($userPath));
            $this->assertNotSame($before->{'salt'}, $updated->{'salt'});
            $this->assertSame('', $updated->{'temporarypassword'});
            $this->assertSame(0, $updated->{'temporaryvalidity'});

            $this->path = $this->getApi().'/authapi/temporarylogin';
            $this->POST = ['requestbody' => json_encode(['user' => $user, 'token' => $token])];
            $this->assertSame(401, $this->request('POST', $this->path)->getCode());
        } finally {
            if (file_exists($userPath)) {
                unlink($userPath);
            }
        }
    }

    public function testResetPasswordIsRateLimitedByIp(): void
    {
        $this->path = $this->getApi().'/authapi/resetpassword';
        $user = 'resetpassword@example.com';
        $userFile = $this->getPrivateDirPath().'/users/'.$user.'.json';
        copy($this->getPrivateDirPath().'/save/'.$user.'.json', $userFile);

        $ip = 'test-reset-'.bin2hex(random_bytes(8));
        $throttleFile = $this->getPrivateDirPath().'/users/history/reset-'.hash('sha256', $ip).'.json';
        $historyFile = $this->getPrivateDirPath().'/users/history/'.$user.'.json';
        $originalIp = $_SERVER['REMOTE_ADDR'] ?? null;
        $_SERVER['REMOTE_ADDR'] = $ip;
        $this->POST = ['requestbody' => '{"user":"'.$user.'","password":"irrelevant"}'];

        try {
            for ($attempt = 0; $attempt < 2; $attempt++) {
                $this->assertSame(200, $this->request('POST', $this->path)->getCode());
            }

            $response = $this->request('POST', $this->path);
            $this->assertSame(429, $response->getCode());
            $this->assertGreaterThan(0, $response->getResult()->{'retryAfter'});
        } finally {
            if (file_exists($throttleFile)) {
                unlink($throttleFile);
            }
            if (file_exists($historyFile)) {
                unlink($historyFile);
            }
            if (file_exists($userFile)) {
                unlink($userFile);
            }
            if ($originalIp === null) {
                unset($_SERVER['REMOTE_ADDR']);
            } else {
                $_SERVER['REMOTE_ADDR'] = $originalIp;
            }
        }
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
