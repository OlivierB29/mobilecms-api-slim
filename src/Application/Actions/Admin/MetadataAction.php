<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin;

use App\Infrastructure\Services\ContentService;
use App\Infrastructure\Utils\JsonUtils;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Get type description.
 */
class MetadataAction extends AdminAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $response = $this->getDefaultResponse();

        $this->checkConfiguration();

        $service = new ContentService($this->getPrivateDirPath());
        $response->setResult(JsonUtils::readJsonFile($service->getMetadataFileName($this->getParam('type'))));
        $response->setCode(200);

        return $this->withResponse($response);
    }
}
