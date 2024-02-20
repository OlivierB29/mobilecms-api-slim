<?php

declare(strict_types=1);

namespace App\Application\Actions\Cms;

use App\Infrastructure\Services\FileService;
use App\Infrastructure\Utils\FileUtils;
use Psr\Http\Message\ResponseInterface as Response;

class ContentDeleteByIdAction extends CmsAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        //delete media
        $fileservice = new FileService($this->getPublicDirPath());
        $mediadir = $fileservice->getRecordDirectoryWithoutRecord($this->getMediaDirPath(), $this->resolveArg('type'), $this->getParam('id'));
        unset($fileservice);
        if (\file_exists($mediadir)) {
            $fileutils = new FileUtils();
            $fileutils->deleteDir($mediadir);
        }

        //delete record
        $response = $this->getService()->deleteRecord($this->resolveArg('type'), $this->resolveArg('id'));
        // step 1 : update Record

        if ($response->getCode() === 200) {
            // step 2 : publish to index
            $response = $this->getService()->rebuildIndex($this->resolveArg('type'), self::ID);
        }

        // delete a record and update the index. eg : /mobilecmsapi/v1/content/calendar/1.json
        return $this->withResponse($response);
    }
}
