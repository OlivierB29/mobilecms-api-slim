<?php
declare(strict_types=1);

namespace App\Application\Actions\Auth;

use Psr\Http\Message\ResponseInterface as Response;
use App\Infrastructure\Services\FileService;
use App\Infrastructure\Services\AuthService;

class AuthenticateAction extends AuthAction
{

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $response = $this->getDefaultResponse();

        try {
            // error if wrong configuration, such as empty directory
            $this->checkConfiguration();


            if (empty($this->request->getParsedBody())) {
                throw new \Exception('no login request');
            }
            // login and get token
            // eg : { "user": "test@example.com", "password":"Sample#123456"}
            $logindata = $this->request->getParsedBody();

            if (!isset($logindata->{'password'})) {
                throw new \Exception('no password data');
            }
            $service = new AuthService($this->getPrivateDirPath() . '/users');
            $response = $service->getToken($this->getUser($logindata), $logindata->{'password'});
            unset($logindata);
            // free variables before response
        } catch (\Exception $e) {
            $response->setError(401, $e->getMessage());
            // @codeCoverageIgnoreStart
        } finally {
            // @codeCoverageIgnoreEnd
        }
        return $this->withResponse($response);
    }
}
