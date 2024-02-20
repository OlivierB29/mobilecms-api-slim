<?php

declare(strict_types=1);

namespace App\Application\Actions\Cms;

use App\Infrastructure\Utils\JsonUtils;
use Psr\Http\Message\ResponseInterface as Response;

class TemplateGetAction extends CmsAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        return $this->respondWithData(JsonUtils::readJsonFile($this->getService()->getTemplateFileName($this->getParam('type'))));
    }
}
