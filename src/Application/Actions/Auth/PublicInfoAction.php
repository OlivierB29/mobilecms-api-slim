<?php
declare(strict_types=1);

namespace App\Application\Actions\Auth;


use Psr\Http\Message\ResponseInterface as Response;
use App\Infrastructure\Services\FileService;
use App\Infrastructure\Services\AuthService;


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
            unset($user);
        
                return $this->withResponse( $response);
    }
}
