<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin;

use App\Infrastructure\Services\ContentService;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Get users types and others.
 */
class AdminTypesGetAction extends AdminAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $response = $this->getDefaultResponse();
        $service = new ContentService($this->getPrivateDirPath());
        $response->setResult($service->adminOptions('types.json'));
        $response->setCode(200);

        return $this->withResponse($response);
    }
}
