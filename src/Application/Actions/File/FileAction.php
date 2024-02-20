<?php
declare(strict_types=1);

namespace App\Application\Actions\File;

use App\Application\Actions\RestAction;

use Psr\Log\LoggerInterface;
use App\Infrastructure\Services\FileService;
use App\Infrastructure\Services\ContentService;
use App\Infrastructure\Utils\Properties;
use App\Infrastructure\Utils\ImageUtils;

use App\Infrastructure\Rest\Response;
use App\Infrastructure\Utils\FileUtils;

abstract class FileAction extends RestAction
{

    /**
     * Media directory (eg: media ).
     */
    protected $media;

    /**
     * Default umask for directories and files.
     */
    protected $umask = 0775;

    protected $files;

    protected $debug;

    protected $thumbnailsizes = [];

    protected $pdfthumbnailsizes = [];

    protected $pdfimagequality = 80;


    protected $fileExtensions = [];

    protected $imagequality = 100;

    protected $imagedriver = 'gd';



    protected $filesService;

    protected $contentservice;



    /**
     * Get a service
     */
    protected function getFileService(): FileService
    {
        if ($this->filesService == null) {
            $this->filesService = new FileService($this->getPublicDirPath());
        }
        
        return $this->filesService;
    }



 

    /**
     * Init configuration.
     *
     */
    public function initConf()
    {
        $this->media = $this->getConf()->{'media'};
        $this->thumbnailsizes = $this->getConf()->{'thumbnailsizes'};
        $this->pdfthumbnailsizes = [100, 200];
        $this->fileExtensions = $this->getConf()->{'fileextensions'};
        $this->imagequality = $this->getProperties()->getInteger('imagequality', 100);

        if (!empty($this->getProperties()->getString('imagedriver'))) {
            $this->imagedriver = $this->getProperties()->getString('imagedriver');
        }
    }



    public function setFiles(array $files = null)
    {
        // Useful for tests
        // http://stackoverflow.com/questions/21096537/simulating-http-request-for-unit-testing

        // set reference to avoid objet clone
        if ($files !== null) {
            $this->files = &$files;
        } else {
            $this->files = &$_FILES;
        }
    }





    /**
     * Main storage directory.
     *
     * @return string eg : // /var/www/html/media
     */
    public function getMediaDirPath(): string
    {
        return $this->getRootDir() . $this->getConf()->{'media'};
    }

    /**
     * Record storage directory.
     *
     * @return string eg : // /var/www/html/media/calendar/1
     */
    public function getRecordDirPath($type, $id): string
    {
        return $this->getMediaDirPath() . '/' . $type . '/' . $id;
    }



   
    /**
     * Get file info and build JSON response.
     *
     * @param string $destfile : file
     * @param string $title    : title of file
     * @param string $url      : url
     */
    protected function getFileResponse($destfile, $title, $url): \stdClass
    {
        $result = null;
        $utils = new ImageUtils();

        if ($utils->isImage($destfile)) {
            $result = $utils->imageInfo($destfile);
        } else {
            $result = \json_decode('{}');
            $fileutils = new FileUtils();
            $result->{'mimetype'} = $fileutils->getMimeType($destfile);
        }
        $result->{'url'} = $url;
        $result->{'size'} = filesize($destfile);
        $result->{'title'} = $title;

        return $result;
    }

    /**
     * Verify minimal configuration.
     */
    protected function checkConfiguration()
    {
        if (!isset($this->getConf()->{'media'})) {
            throw new \Exception('Empty media dir');
        }
    }


    /**
    * enable debug
    * @param bool $value enable debug
    */
    public function setDebug(bool $value)
    {
        $this->debug = $value;
    }



    /**
    * Basic upload verification
    * @param string $file file name
    * @return bool
    */
    protected function isAllowedExtension(string $file): bool
    {
        $result = false;
        if ($file !== '') {
            $result = in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), $this->fileExtensions);
        }
        return $result;
    }

    /**
    * Basic upload verification
    * @param string $file file name
    * @return string
    */
    protected function getExtension(string $file): string
    {
        $result = false;
        if ($file !== '') {
            $result = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        }
        return $result;
    }


    /**
     * Get a service
     */
    protected function getContentService(): ContentService
    {
        if ($this->contentservice == null) {
            $this->contentservice = new ContentService($this->getPublicDirPath());
        }
        
        return $this->contentservice;
    }


    /**
     * Delete files.
     *
     * @param string $datatype news
     * @param string $id       123
     * @param array $files : [{ "url": "http://something.com/[...]/foobar.html" }]
     * @return Response rest response
     */
    protected function deleteMediaFiles(string $datatype, string $id, array $files): Response
    {
        $response = $this->getDefaultResponse();


        $result = [];

        $tmpRecord = $this->getContentService()->getRecord($datatype, $id);
        if ($tmpRecord == null) {
            throw new \Exception('Record not found');
        }
        




        foreach ($files as $formKey => $file) {

            if (\property_exists($tmpRecord->getResult(), 'media')) {


            foreach ($tmpRecord->getResult()->{'media'} as $media => $fileInRecord) {
                if ($fileInRecord->url === $file->url) {
                    foreach ($fileInRecord->thumbnails as $thumbnails => $thumbnailFile) {
                        $thumbnailPath = $this->getMediaDirPath() . '/' . $datatype . '/' . $id . '/thumbnails' . '/'  . $thumbnailFile->url;
                        if (file_exists($thumbnailPath)) {
                            if (!unlink($thumbnailPath)) {
                                throw new \Exception('delete ' . $thumbnailPath . ' KO');
                            }
                        } 
    
                    }
                }
    
            }
            }
            // /var/www/html/media/calendar/1
            $destdir = $this->getRecordDirPath($datatype, $id);

            // upload
            if (isset($file->{'url'})) {
                // get foobar.html from http://something.com/[...]/foobar.html
                $destfile = $destdir . '/' . basename($file->{'url'});
                if (file_exists($destfile)) {
                    if (!unlink($destfile)) {
                        throw new \Exception('delete ' . $file['url'] . ' KO');
                    }
                } else {
                    // TODO add message
                }
            } else {
                throw new \Exception('wrong file ' . $file['url'] . ' KO');
            }
        }



        $uploadResult = $this->getFileService()->getDescriptions($destdir);


        $response->setResult($uploadResult);
        $response->setCode(200);

        return $response;
    }
}
