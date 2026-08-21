<?php

namespace Tests;

use App\Infrastructure\Services\AuthService;

abstract class AuthApiUtility extends ApiUtility
{
    protected $user;
    protected $token;
    protected $conf;


    protected function setUp(): void
    {
        parent::setUp();
        $this->memory1 = 0;
        $this->memory2 = 0;

        $this->conf = json_decode(file_get_contents('tests/conf.json'));

        $this->memory();

    }

    protected function setGuest()
    {
                $service = new AuthService(realpath('tests-data').$this->conf->{'privatedir'}.'/users');

                $response = $service->getToken('guest@example.com', 'Sample#123456');
        $this->user = $response->getResult();
        $this->token = 'Bearer '.$this->user->{'token'};
        $this->headers['Authorization'] = $this->token;
    }

    protected function setEditor()
    {
        $service = new AuthService(realpath('tests-data').$this->conf->{'privatedir'}.'/users');

        $response = $service->getToken('editor@example.com', 'Sample#123456');
        $this->user = $response->getResult();
        $this->token = 'Bearer '.$this->user->{'token'};
        $this->headers['Authorization'] = $this->token;
    }

    protected function setAdmin()
    {
        $service = new AuthService(realpath('tests-data').$this->conf->{'privatedir'}.'/users');

        $response = $service->getToken('admin@example.com', 'Sample#123456');
        $this->user = $response->getResult();
        $this->token = 'Bearer '.$this->user->{'token'};
        $this->headers['Authorization'] = $this->token;
    }
}
