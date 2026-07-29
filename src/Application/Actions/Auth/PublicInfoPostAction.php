<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Infrastructure\Services\AuthService;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Get minimal information of an account.
 */
class PublicInfoPostAction extends AuthAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $response = $this->getDefaultResponse();

        //throw error if wrong configuration, such as empty directory
        $this->checkConfiguration();

        $service = new AuthService($this->getPrivateDirPath().'/users');

        $userdata = $this->getRequestBody();
        if (isset($userdata->{AuthAction::LOGIN_USER})) {
            $response = $service->getPublicInfo($userdata->{AuthAction::LOGIN_USER});
        }

        return $this->withResponse($response);
    }
}
