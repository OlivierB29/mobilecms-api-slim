<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Infrastructure\Services\AuthService;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Complete a password reset using its one-time link token.
 */
class ConfirmPasswordResetAction extends AuthAction
{
    protected function action(): Response
    {
        $this->checkConfiguration();
        $logindata = $this->getRequestBody();

        if (!isset($logindata->{'user'}, $logindata->{'token'}, $logindata->{'newpassword'})
            || !is_string($logindata->{'user'})
            || !is_string($logindata->{'token'})
            || !is_string($logindata->{'newpassword'})
            || $logindata->{'newpassword'} === '') {
            $response = $this->getDefaultResponse();
            $response->setError(400, 'Missing reset token or new password');

            return $this->withResponse($response);
        }

        $service = new AuthService($this->getPrivateDirPath().'/users');
        $response = $service->completePasswordReset(
            $this->getUser($logindata),
            $logindata->{'token'},
            $logindata->{'newpassword'}
        );
        unset($logindata);

        return $this->withResponse($response);
    }
}
