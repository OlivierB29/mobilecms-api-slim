<?php
declare(strict_types=1);

namespace App\Application\Actions\Admin;

use Psr\Http\Message\ResponseInterface as Response;

use App\Infrastructure\Services\ContentService;

/**
 * User index
 */
class AdminIndexGetAction extends AdminAction
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
  
        $response = $service->getAll($this->getParam('type') . '/index/index.json');

        


        return $this->withResponse($response);
    }
}
