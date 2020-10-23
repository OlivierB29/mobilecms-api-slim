<?php
declare(strict_types=1);

namespace App\Application\Actions\File;


use Psr\Http\Message\ResponseInterface as Response;
use App\Infrastructure\Services\FileService;

use App\Infrastructure\Utils\FileUtils;

class BasicUploadPostAction extends FileAction
{

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {         

        //get the full data of a single record
        // eg : /mobilecmsapi/v1/file/calendar/1
        $uploadResult = $this->uploadFiles($this->getParam('type'), $this->getParam('id'));
        $response->setCode(200);

        $response->setResult($uploadResult);
               
                return $this->response($this->request, $response);
    }
}
