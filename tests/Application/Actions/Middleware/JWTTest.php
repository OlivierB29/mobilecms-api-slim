<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Middleware;

use App\Infrastructure\Rest\JwtToken;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Tests\ApiUtility;


final class JWTTest extends ApiUtility
{
    private $util;

    private $allowed_algs;

    protected function setUp(): void
    {
        $this->util = new JwtToken();
        $this->util->setAlgorithm('sha512');
        $this->allowed_algs = ['HS512'];
    }

    public function testBasic()
    {
        $username = 'test';
        $email = 'test@example.com';
        $role = 'guest';
        $key = str_repeat('a', 64);
        $alg = 'HS512';

        $token = $this->util->createTokenFromUser($username, $email, $role, $key);

        $payload = $this->util->initPayloadArray($username, $email, $role);

        $phpjwtToken = JWT::encode($payload, $key, $alg);

        $this->assertTrue($token != null);
        $this->assertTrue(strlen($token) > 100);
        $this->assertTrue(strlen($phpjwtToken) > 100);
    }

    /* TODO
    public function testVerifyToken()
    {

        $username = 'test';
        $email = 'test@example.com';
        $role = 'guest';
        $key = str_repeat('a', 64);
        $alg = 'HS512';

        $token = $this->util->createTokenFromUser($username, $email, $role, $key);


        $payload = $this->util->initPayload($username, $email, $role);

        $phpjwtToken = JWT::encode($payload, $key, $alg);


        $this->assertTrue(
            $this->util->verifyToken($token, $key)
        );
        //$jwt, $key, array $allowed_algs = array()
        $jwtPayload = JWT::decode($phpjwtToken, $key, $this->allowed_algs);


        $this->assertEquals($phpjwtToken, $jwtPayload);
    }
*/
    public function testVerifyWrongSecret()
    {
        $username = 'test';
        $email = 'test@example.com';
        $role = 'guest';
        $key = str_repeat('a', 64);
        $alg = 'HS512';

        $token = $this->util->createTokenFromUser($username, $email, $role, $key);

        $payload = $this->util->initPayloadArray($username, $email, $role);

        $phpjwtToken = JWT::encode($payload, $key, $alg);

        $this->assertFalse(
            $this->util->verifyToken($token, str_repeat('b', 64))
        );
        //$jwt, $key, array $allowed_algs = array()

        $this->expectException(\Exception::class);

        JWT::decode($phpjwtToken, new Key(str_repeat('b', 64), $alg));
    }
}
