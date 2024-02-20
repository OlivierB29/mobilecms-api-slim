<?php

declare(strict_types=1);

namespace App\Application\Actions\Cms;

use Psr\Http\Message\ResponseInterface as Response;

class StatusGetAction extends CmsAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        return $this->respondWithData(json_decode('{"result":"true"}'));
    }
}
