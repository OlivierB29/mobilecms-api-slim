<?php
declare(strict_types=1);

namespace App\Application\Actions\File;

use App\Application\Actions\RestAction;

use Psr\Log\LoggerInterface;
use App\Infrastructure\Services\FileService;
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

    protected $imagick = false;



    protected $service;



    /**
     * Get a service
     */
    protected function getService(): FileService
    {
        if ($this->service == null) {
            $this->service = new FileService();
        }
        
        return $this->service;
    }


    /**
     * Init configuration.
     *
     */
    public function initConf()
    {
        $this->media = $this->getConf()->{'media'};
        $this->thumbnailsizes = $this->getConf()->{'thumbnailsizes'};
        // width 214 * height 82
        $this->pdfthumbnailsizes = [100, 200];
        $this->fileExtensions = $this->getConf()->{'fileextensions'};
        $this->imagequality = $this->getProperties()->getInteger('imagequality', 100);

        $this->imagick = $this->getProperties()->getBoolean('imagick', false);
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
     * Delete files.
     *
     * @param string $datatype news
     * @param string $id       123
     * @param array $files : [{ "url": "http://something.com/[...]/foobar.html" }]
     * @return Response rest response
     */
    protected function deleteFiles(string $datatype, string $id, array $files): Response
    {
        $response = $this->getDefaultResponse();


        $result = [];

        foreach ($files as $formKey => $file) {
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

        $response->setResult($result);
        $response->setCode(200);

        return $response;
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
}
