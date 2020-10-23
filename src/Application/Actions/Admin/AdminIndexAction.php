<?php
declare(strict_types=1);

namespace App\Application\Actions\Admin;


use Psr\Http\Message\ResponseInterface as Response;

use App\Infrastructure\Services\ContentService;
use App\Infrastructure\Utils\FileUtils;

class AdminIndexAction extends AuthAction
{

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $userKey = 'email';
        $response = $this->getDefaultResponse();

        $this->checkConfiguration();

            $service = new ContentService($this->getPrivateDirPath());

            // eg : /mobilecmsapi/v1/content/calendar
            if ($this->requestObject->method === 'GET') {
                $response = $service->getAll($this->getParam('type') . '/index/index.json');
            } elseif ($this->requestObject->method === 'POST') {
                $response = $service->rebuildIndex($this->getParam('type'), $userKey);
            }
        


                return $this->response($this->request, $response);
    }
}
