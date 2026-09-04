<?php

declare(strict_types=1);

namespace App\Application\Actions\File;

use App\Application\Actions\RestAction;
use App\Infrastructure\Rest\Response;
use App\Infrastructure\Services\ContentService;
use App\Infrastructure\Services\FileService;
use App\Infrastructure\Utils\FileUtils;
use App\Infrastructure\Utils\ImageUtils;

abstract class FileAction extends RestAction
{
    /**
     * Media directory (eg: media ).
     */
    protected $media;

    /**
     * Default umask for directories and files.
     */
    protected $filesUmask = 0644;
    
    protected $directoryUmask = 0750;

    protected $files;

    protected $debug;

    protected $thumbnailsizes = [];

    protected $pdfthumbnailsizes = [];

    protected $pdfimagequality = 80;

    protected $fileExtensions = [];

    protected $fileMimeTypes = [];

    protected $uploadMaxFileSize = 0;

    protected $imagequality = 100;

    protected $imagedriver = 'gd';

    protected $filesService;

    protected $contentservice;

    /**
     * Get a service.
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
     */
    public function initConf()
    {
        $this->media = $this->getConf()->{'media'};
        $this->thumbnailsizes = $this->getConf()->{'thumbnailsizes'};
        $this->pdfthumbnailsizes = [100, 200];
        $this->fileExtensions = $this->getConf()->{'fileextensions'};
        $this->fileMimeTypes = $this->getConf()->{'mimetypes'} ?? [];
        $this->uploadMaxFileSize = $this->parseFileSize($this->getConf()->{'uploadmaxfilesize'} ?? 0);
        $this->imagequality = $this->getProperties()->getInteger('imagequality', 100);

        if (!empty($this->getProperties()->getString('imagedriver'))) {
            $this->imagedriver = $this->getProperties()->getString('imagedriver');
        }
    }

    protected function parseFileSize($value): int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }

        if (!preg_match('/^\s*(\d+(?:\.\d+)?)\s*(B|KB|MB|GB)?\s*$/i', (string) $value, $matches)) {
            return 0;
        }

        $units = ['B' => 0, 'KB' => 1, 'MB' => 2, 'GB' => 3];
        $power = $units[strtoupper($matches[2] ?? 'B')];

        return (int) round((float) $matches[1] * (1024 ** $power));
    }

    public function setFiles(array $files)
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
        return $this->fileutils->concatDirectories($this->getRootDir(), $this->getConf()->{'media'});
    }

    /**
     * Record storage directory.
     *
     * @return string eg : // /var/www/html/media/calendar/1
     */
    public function getRecordDirPath($type, $id): string
    {
        return $this->fileutils->concatDirectories($this->getMediaDirPath(), $type).'/'.$id;
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
     * enable debug.
     *
     * @param bool $value enable debug
     */
    public function setDebug(bool $value)
    {
        $this->debug = $value;
    }

    /**
     * Basic upload verification.
     *
     * @param string $file file name
     *
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
     * Basic upload verification.
     *
     * @param string $file file name
     *
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
     * Get a service.
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
     * @param array  $files    : [{ "url": "http://something.com/[...]/foobar.html" }]
     *
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

        foreach ($files as $file) {
            if (\property_exists($tmpRecord->getResult(), 'media')) {
                foreach ($tmpRecord->getResult()->{'media'} as $fileInRecord) {
                    if ($fileInRecord->url === $file->url) {
                        $this->deleteThumbailFiles($fileInRecord, $datatype, $id);
                    }
                }
            }
            // /var/www/html/media/calendar/1
            $destdir = $this->getRecordDirPath($datatype, $id);

            // upload
            if (isset($file->{'url'})) {
                // get foobar.html from http://something.com/[...]/foobar.html
                $destfile = $destdir.'/'.basename($file->{'url'});
                if (file_exists($destfile)) {
                    if (!unlink($destfile)) {
                        throw new \Exception('delete '.$file['url'].' KO');
                    }
                } else {
                    // TODO add message
                }
            } else {
                throw new \Exception('wrong file '.$file['url'].' KO');
            }
        }

        $uploadResult = $this->getFileService()->getDescriptions($destdir);

        $response->setResult($uploadResult);
        $response->setCode(200);

        return $response;
    }

    protected function deleteThumbailFiles(stdClass $fileInRecord, string $datatype, string $id): bool {
        

    if ($fileInRecord->thumbnails !== null) {
                        foreach ($fileInRecord->thumbnails as $thumbnailFile) {
                            $thumbnailPath = $this->getMediaDirPath().'/'.$datatype.'/'.$id.'/thumbnails'.'/'.$thumbnailFile->url;
                            if (file_exists($thumbnailPath)) {
                                if (!unlink($thumbnailPath)) {
                                    throw new \Exception('delete '.$thumbnailPath.' KO');
                                }
                            }
                        }
    }

    return true;
    }
}
