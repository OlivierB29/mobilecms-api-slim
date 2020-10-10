<?php
declare(strict_types=1);

namespace App\Application\Actions\Cms;

use Psr\Http\Message\ResponseInterface as Response;

use App\Infrastructure\Utils\JsonUtils;

class MetadataGetAction extends CmsAction
{

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        return $this->respondWithData(JsonUtils::readJsonFile($this->getService()->getMetadataFileName($this->getParam('type'))));

    }
}
