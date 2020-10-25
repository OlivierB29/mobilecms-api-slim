<?php
declare(strict_types=1);

namespace App\Application\Actions\File;
use Slim\Exception\HttpBadRequestException;//400
use Slim\Exception\HttpNotFoundException;//404
use Slim\Exception\HttpInternalServerErrorException;//500
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\UploadedFileInterface;
use App\Infrastructure\Services\FileService;

use App\Infrastructure\Utils\FileUtils;

class BasicUploadPostAction extends FileAction
{

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {         
        $response = $this->getDefaultResponse();
        $this->initConf();
        //get the full data of a single record
        // eg : /mobilecmsapi/v1/file/calendar/1
        $files = $this->request->getUploadedFiles();
        $uploadResult = $this->uploadFilesSlim($this->getParam('type'), $this->getParam('id'), $files);
        $response->setCode(200);

        $response->setResult($uploadResult);
               
                return $this->withResponse( $response);
    }


    private function uploadFilesSlim($type, $id, $files): array
    {

        $result = [];

        
        if (!isset($files) || count($files) === 0) {
            throw new HttpBadRequestException($this->request, 'no file.');
        }
        
        // Basic upload verification
        foreach ($files as $fileControl) {
            
            if (!$this->isAllowedExtension($fileControl->getClientFilename())) {
                throw new HttpBadRequestException($this->request, 'forbidden file type');
            }
        }

        foreach ($files as $file) {
            $destdir = $this->getRecordDirPath($type, $id);

            // create directory if it doesn't exist
            if (!file_exists($destdir)) {
                mkdir($destdir, $this->umask, true);
                chmod($destdir, $this->umask);
            }

            // upload
            if ($file->getClientFilename() !== null) {
                $destfile = $destdir . '/' . $file->getClientFilename();
  

                $file->moveTo($destfile);
                if (!file_exists($destfile)) {
                    throw new HttpInternalServerErrorException($this->request, 'Upload error ' . $file->getClientFilename());
                }
   

     
                    chmod($destfile, $this->umask);
                    $title = $file->getClientFilename();
                    $url = $file->getClientFilename();

                    $fileResult = $this->getFileResponse($destfile, $title, $url);

                    array_push($result, $fileResult);

            }
        }

        return $result;
    }
}
