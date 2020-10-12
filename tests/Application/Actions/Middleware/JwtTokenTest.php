<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Middleware;
use Tests\TestCase;
use App\Infrastructure\Rest\JwtToken;

final class JwtTokenTest extends TestCase
{
    private $util;

    protected function setUp(): void
    {
        $this->util = new JwtToken();
        $this->util->setAlgorithm('sha512');
    }

    public function testBasic()
    {
        $token = $this->util->createTokenFromUser('test', 'test@example.com', 'guest', 'secret');
        $this->assertTrue($token != null);
        $this->assertTrue(strlen($token) > 100);
    }

    public function testVerifyToken()
    {
        $token = $this->util->createTokenFromUser('test', 'test@example.com', 'guest', 'secret');

        $this->assertTrue(
            $this->util->verifyToken($token, 'secret')
        );
    }

    public function testVerifyWrongSecret()
    {
        $token = $this->util->createTokenFromUser('test', 'test@example.com', 'guest', 'secret');

        $this->assertFalse(
            $this->util->verifyToken($token, 'wrongsecret')
        );
    }
}
