<?php
declare(strict_types=1);

namespace App\Application\Actions\Cms;

use Psr\Http\Message\ResponseInterface as Response;

class ContentGetByIdAction extends CmsAction
{

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        return $this->withResponse($this->getService()->getRecord($this->resolveArg('type'), $this->resolveArg('id')));
    }
}
