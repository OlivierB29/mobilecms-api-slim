<?php
declare(strict_types=1);

namespace App\Application\Actions\File;

use Psr\Http\Message\ResponseInterface as Response;
use App\Infrastructure\Services\FileService;

class ThumbnailsAction extends FileAction
{

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $response = $this->getDefaultResponse();
        
        $this->initConf();
        $this->checkConfiguration();

        $service = new FileService();
        $files = $this->getRequestBody();
        $response = $service->createThumbnails(
            $this->getMediaDirPath(),
            $this->getParam('type'),
            $this->getParam('id'),
            $files,
            $this->thumbnailsizes,
            $this->imagequality,
            $this->pdfthumbnailsizes,
            $this->pdfimagequality
        );


        return $this->withResponse($response);
    }
}
