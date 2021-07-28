<?php
declare(strict_types=1);

namespace App\Application\Actions\File;

use Psr\Http\Message\ResponseInterface as Response;
use App\Infrastructure\Services\FileService;

class BasicUploadGetAction extends FileAction
{

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $response = $this->getDefaultResponse();
        // create service
        $service = new FileService($this->getPublicDirPath());

        // update files description
        // /var/www/html/media/calendar/1
        $destdir = $this->getRecordDirPath($this->getParam('type'), $this->getParam('id'));

        $uploadResult = $service->getDescriptions($destdir);
        $response->setCode(200);

        $response->setResult($uploadResult);
        return $this->withResponse($response);
    }
}
