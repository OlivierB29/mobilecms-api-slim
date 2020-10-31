<?php
declare(strict_types=1);

namespace App\Application\Actions\File;

use Psr\Http\Message\ResponseInterface as Response;
use App\Infrastructure\Services\FileService;

class DeleteAction extends FileAction
{

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $response = $this->getDefaultResponse();

        $this->checkConfiguration();

        $response = $this->deleteFiles(
            $this->getParam('type'),
            $this->getParam('id'),
            $this->getRequestBody()
        );



        return $this->withResponse($response);
    }
}
