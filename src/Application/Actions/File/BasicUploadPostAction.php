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

// UploadedFileInterface 
    protected function uploadFilesSlim($type, $id, $files): array
    {
        /*
      File properties example
      - name:1.jpg
      - type:image/jpeg
      - tmp_name:/tmp/phpzDc6qT
      - error:0
      - size:700
        */
        $result = [];
        // $_FILES
        
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
                $moveResult = false;
                // why not inline notation condition ? a : b;
                // If an exception is thrown with IO, I prefer to be sure of the line in error.

/*
                if ($this->debug) {
                    $moveResult = rename($file->getClientFilename(), $destfile);
                } else {
                    $moveResult = move_uploaded_file($file->getClientFilename(), $destfile);
                }
*/

                $file->moveTo($destfile);
                if (!file_exists($destfile)) {
                    throw new HttpInternalServerErrorException($this->request, 'Upload error ' . $file->getClientFilename());
                }
                $moveResult = true;

                if ($moveResult) {
                    chmod($destfile, $this->umask);
                    $title = $file->getClientFilename();
                    $url = $file->getClientFilename();

                    $fileResult = $this->getFileResponse($destfile, $title, $url);

                    array_push($result, $fileResult);
                } else {
                    throw new HttpInternalServerErrorException($this->request,$file->getClientFilename() . ' KO');
                }
            }
        }

        if (count($result) === 0) {
            throw new HttpInternalServerErrorException($this->request,'no file uploaded. Please check file size.');
        }

        return $result;
    }
}
