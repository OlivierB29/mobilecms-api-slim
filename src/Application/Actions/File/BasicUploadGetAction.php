<?php
declare(strict_types=1);

namespace App\Application\Actions\File;


use Psr\Http\Message\ResponseInterface as Response;
use App\Infrastructure\Services\FileService;

use App\Infrastructure\Utils\FileUtils;

class BasicUploadGetAction extends FileAction
{

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
                // create service
                $service = new FileService();

                // update files description
                // /var/www/html/media/calendar/1
                $destdir = $this->getRecordDirPath($this->getParam('type'), $this->getParam('id'));

                $uploadResult = $service->getDescriptions($destdir);
                $response->setCode(200);

                $response->setResult($uploadResult);
                return $this->response($this->request, $response);
    }
}
