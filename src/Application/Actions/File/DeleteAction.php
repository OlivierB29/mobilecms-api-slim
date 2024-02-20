<?php

declare(strict_types=1);

namespace App\Application\Actions\File;

use Psr\Http\Message\ResponseInterface as Response;

class DeleteAction extends FileAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        // RestResponse
        $response = $this->getDefaultResponse();

        $this->checkConfiguration();

        // Response
        $response = $this->deleteMediaFiles(
            $this->getParam('type'),
            $this->getParam('id'),
            $this->getRequestBody()
        );

        return $this->withResponse($response);
    }
}
