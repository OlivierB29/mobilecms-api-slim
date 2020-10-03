<?php
declare(strict_types=1);

namespace App\Application\Actions\Cms;

use Psr\Http\Message\ResponseInterface as Response;

class IndexPostAction extends CmsAction
{

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $oldResponse = $this->getService()->rebuildIndex($this->resolveArg('type'), self::ID);
        return $this->respondWithData($users);
    }
}
