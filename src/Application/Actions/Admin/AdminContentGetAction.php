<?php
declare(strict_types=1);

namespace App\Application\Actions\Admin;


use Psr\Http\Message\ResponseInterface as Response;

use App\Infrastructure\Services\ContentService;

use App\Infrastructure\Services\AuthService;

class AdminContentGetAction extends AdminAction
{

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $response = $this->getDefaultResponse();

        $this->checkConfiguration();

        $service = new ContentService($this->getPrivateDirPath());
        $authService = new AuthService($this->getPrivateDirPath() . '/users');

        $tmpResponse = $service->getRecord($this->getParam('type'), $this->getParam('id'));
        // basic user fields, without password
        if ($tmpResponse->getCode() === 200) {
            $response->setCode(200);
            $response->setResult($this->getUserResponse($tmpResponse->getResult()));
        }
                return $this->response($this->request, $response);
    }
}
