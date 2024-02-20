<?php

declare(strict_types=1);

namespace App\Application\Actions\File;

use App\Infrastructure\Services\FileService;
use Psr\Http\Message\ResponseInterface as Response;

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

        $service = new FileService($this->getPublicDirPath());
        $files = $this->getRequestBody();
        $response = $service->createThumbnails(
            $this->getMediaDirPath(),
            $this->getParam('type'),
            $this->getParam('id'),
            $files,
            $this->thumbnailsizes,
            $this->imagequality,
            $this->pdfthumbnailsizes,
            $this->pdfimagequality,
            $this->imagedriver
        );

        return $this->withResponse($response);
    }
}
