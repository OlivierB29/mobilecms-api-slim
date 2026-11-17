<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Infrastructure\Services\AuthService;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Validate a password reset link before displaying the change-password form.
 */
class TemporaryLoginAction extends AuthAction
{
    protected function action(): Response
    {
        $this->checkConfiguration();
        $logindata = $this->getRequestBody();

        if (!isset($logindata->{'user'}, $logindata->{'token'})
            || !is_string($logindata->{'user'})
            || !is_string($logindata->{'token'})) {
            $response = $this->getDefaultResponse();
            $response->setError(400, 'Missing user email or reset token');

            return $this->withResponse($response);
        }

        $service = new AuthService($this->getPrivateDirPath().'/users');
        $response = $service->validateTemporaryLogin(
            $this->getUser($logindata),
            $logindata->{'token'}
        );

        return $this->withResponse($response);
    }
}
