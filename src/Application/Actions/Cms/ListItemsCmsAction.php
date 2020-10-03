<?php
declare(strict_types=1);

namespace App\Application\Actions\Cms;

use Psr\Http\Message\ResponseInterface as Response;

class ListItemsCmsAction extends CmsAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {

        return $this->respondWithData($users);
    }
}
