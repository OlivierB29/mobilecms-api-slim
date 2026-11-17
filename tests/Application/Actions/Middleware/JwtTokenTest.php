<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Middleware;

use App\Infrastructure\Rest\JwtToken;
use Tests\ApiUtility;

final class JwtTokenTest extends ApiUtility
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

    public function testGeneratedTokenHasExpirationClaim()
    {
        $token = $this->util->createTokenFromUser('test', 'test@example.com', 'guest', 'secret');
        $payload = json_decode($this->util->getPayload($token), true, 512, JSON_THROW_ON_ERROR);

        $this->assertGreaterThan(time(), $payload['exp']);
    }

    public function testTokenWithoutExpirationIsRejected()
    {
        $header = base64_encode('{"alg":"HS512","typ":"JWT"}');
        $payload = base64_encode('{"sub":"test@example.com","name":"test","role":"guest"}');
        $signature = hash_hmac('sha512', $header.'.'.$payload, $this->util->createSecret('secret'));
        $token = $header.'.'.$payload.'.'.$signature;

        $this->assertFalse($this->util->verifyToken($token, 'secret'));
        $this->expectException(\Exception::class);
        $this->util->decode($token, 'secret');
    }

    public function testExpiredTokenIsRejected()
    {
        $header = base64_encode('{"alg":"HS512","typ":"JWT"}');
        $payload = base64_encode(json_encode([
            'sub' => 'test@example.com',
            'name' => 'test',
            'role' => 'guest',
            'exp' => time() - 1,
        ], JSON_THROW_ON_ERROR));
        $signature = hash_hmac('sha512', $header.'.'.$payload, $this->util->createSecret('secret'));
        $token = $header.'.'.$payload.'.'.$signature;

        $this->assertFalse($this->util->verifyToken($token, 'secret'));
        $this->expectException(\Exception::class);
        $this->util->decode($token, 'secret');
    }

    public function testVerifyWrongSecret()
    {
        $token = $this->util->createTokenFromUser('test', 'test@example.com', 'guest', 'secret');

        $this->assertFalse(
            $this->util->verifyToken($token, 'wrongsecret')
        );
    }
}
