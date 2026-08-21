<?php

declare(strict_types=1);

namespace App\Application\Actions\File;

use Psr\Http\Message\ResponseInterface as Response; //400
//404
use Psr\Http\Message\StreamInterface; //500
use Psr\Http\Message\UploadedFileInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpInternalServerErrorException;

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

    private function uploadFilesSlim($type, $id, $inputFiles): array
    {
        $result = [];

        if (!isset($inputFiles) || count($inputFiles) === 0) {
            throw new HttpBadRequestException($this->request, 'empty file structure.');
        }

        if (!isset($inputFiles['uploadfiles']) || count($inputFiles['uploadfiles']) === 0) {
            throw new HttpBadRequestException($this->request, 'empty files array.');
        }

        // Basic upload verification
        foreach ($inputFiles['uploadfiles'] as $fileControl) {
                    if ($fileControl !== null && !$this->isFileAllowed($fileControl)) {
                        throw new HttpBadRequestException($this->request, 'forbidden file type');
                    }
        }


        foreach ($inputFiles['uploadfiles'] as $file) {
            if ($file !== null) {
                $fileResult = $this->uploadFile($type, $id, $file);
                array_push($result, $fileResult);
            }

        }

        return $result;
    }

    protected function isFileAllowed(UploadedFileInterface $file): bool
    {
        $result = false;
        if ($file !== null) {
            $fileExtension = $this->getExtension($file->getClientFilename());
            $result = $this->isExtensionPermitted($fileExtension);
        }

        return $result;
    }

        /**
     * Basic upload verification.
     *
     * @param string $file file name
     *
     * @return bool
     */
    protected function isExtensionPermitted(string $extension): bool
    {
        $result = false;
        if ($extension !== '') {
            $result = in_array(strtolower($extension), $this->fileExtensions);
        }

        return $result;
    }


    private function writeStream(string $file, StreamInterface $s)
    {
        file_put_contents($file, $s->getContents());
    }

    private function uploadFile(string $type, string $id, UploadedFileInterface $file): \stdClass
    {
        $result = null;
        $destdir = $this->getRecordDirPath($type, $id);

        // create directory if it doesn't exist
        if (!file_exists($destdir)) {
            mkdir($destdir, $this->umask, true);
            chmod($destdir, $this->umask);
        }

        // upload
        if ($file->getClientFilename() !== null) {
            $destfile = $destdir.'/'.$file->getClientFilename();

            if ($this->getExtension($file->getClientFilename()) === 'pdf') {
                $this->writeStream($destfile, $file->getStream());
            } else {
                $file->moveTo($destfile);
            }

            if (!file_exists($destfile)) {
                throw new HttpInternalServerErrorException($this->request, 'Upload error '.$file->getClientFilename());
            }

            chmod($destfile, $this->umask);
            $title = $file->getClientFilename();
            $url = $file->getClientFilename();

            $result = $this->getFileResponse($destfile, $title, $url);
        }

        return $result;
    }
}
