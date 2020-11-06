<?php
declare(strict_types=1);

namespace App\Application\Actions\File;

use Slim\Exception\HttpBadRequestException;//400
use Slim\Exception\HttpNotFoundException;//404
use Slim\Exception\HttpInternalServerErrorException;//500
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\StreamInterface;
use App\Infrastructure\Services\FileService;

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
  
        /* just in case

               if (isset($tmpfiles['uploadfiles1'])) {
            $files = $tmpfiles['uploadfiles1'];
            error_log('Using 1');
        } elseif (isset($files['uploadfiles2'])){
            $files = $tmpfiles['uploadfiles2'];
            error_log('Using 2');
        } else {
            $files = $tmpfiles;
            error_log('Using 0');
        }
*/
        $uploadResult = $this->uploadFilesSlim($this->getParam('type'), $this->getParam('id'), $files);
        $response->setCode(200);

        $response->setResult($uploadResult);
               
        return $this->withResponse($response);
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
        /*
                foreach ($files as $tmpfile) {
                    throw new \Exception("files ? " . $tmpfile->getClientFilename());
                }
        */
        

        foreach ($files as $file) {
            $fileResult = $this->uploadFile($type, $id, $file);
            array_push($result, $fileResult);
        }

        return $result;
    }

    private function writeStream(string $file, StreamInterface $s) {
        file_put_contents($file, $s->getContents());
    }

    private function uploadFile(string $type,string $id, UploadedFileInterface $file) : \stdClass{
        $destdir = $this->getRecordDirPath($type, $id);

        // create directory if it doesn't exist
        if (!file_exists($destdir)) {
            mkdir($destdir, $this->umask, true);
            chmod($destdir, $this->umask);
        }

        // upload
        if ($file->getClientFilename() !== null) {
            $destfile = $destdir . '/' . $file->getClientFilename();

            if ($this->getExtension($file->getClientFilename()) === 'pdf') {
                $this->writeStream($destfile, $file->getStream());

            } else {
                $file->moveTo($destfile);
            }
            
            if (!file_exists($destfile)) {
                throw new HttpInternalServerErrorException($this->request, 'Upload error ' . $file->getClientFilename());
            }


 
            chmod($destfile, $this->umask);
            $title = $file->getClientFilename();
            $url = $file->getClientFilename();

            return $this->getFileResponse($destfile, $title, $url);

        }
    }
}
