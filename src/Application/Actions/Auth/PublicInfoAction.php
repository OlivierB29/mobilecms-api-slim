<?php
declare (strict_types = 1);

namespace App\Application\Actions\Auth;

use App\Infrastructure\Services\AuthService;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Get minimal information of an account
 */
class PublicInfoAction extends AuthAction
{

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $response = $this->getDefaultResponse();

        //throw error if wrong configuration, such as empty directory
        $this->checkConfiguration();

        $service = new AuthService($this->getPrivateDirPath() . '/users');

        $response = $service->getPublicInfo($this->getParam('id'));

        return $this->withResponse($response);
    }
}
