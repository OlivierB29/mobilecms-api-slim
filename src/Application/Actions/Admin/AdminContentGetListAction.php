<?php
declare(strict_types=1);

namespace App\Application\Actions\Admin;


use Psr\Http\Message\ResponseInterface as Response;
use App\Infrastructure\Services\UserService;
use App\Infrastructure\Services\ContentService;
use App\Infrastructure\Utils\FileUtils;

class AdminContentGetListAction extends AdminAction
{

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $response = $this->getDefaultResponse();

        $this->checkConfiguration();


                //get all records in directory
                $userService = new UserService($this->getPrivateDirPath() . '/users');
                $response = $userService->getAllUsers();
                return $this->withResponse( $response);
    }
}
