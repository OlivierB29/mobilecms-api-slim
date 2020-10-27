<?php
declare(strict_types=1);

namespace App\Application\Actions\File;

use Psr\Http\Message\ResponseInterface as Response;
use App\Infrastructure\Rest\Response as RestResponse;
use App\Infrastructure\Services\FileService;

class DownloadAction extends FileAction
{

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $response = $this->getDefaultResponse();

        $this->checkConfiguration();


        $service = new FileService();

 
        $response = $this->downloadFiles(
            $this->getParam('type'),
            $this->getParam('id'),
            $this->getRequestBody()
        );
            
        

        return $this->withResponse($response);
    }

     /**
     * Download files from specified URLs.
     *
     * @param string $datatype : news
     * @param string $id       : 123
     * @param string $filesStr : [{ "url": "http://something.com/[...]/foobar.html" }]
     *
     * @return RestResponse result
     */
    private function downloadFiles(string $datatype, string $id, string $filesStr): RestResponse
    {
        $response = $this->getDefaultResponse();

        $files = json_decode($filesStr);

        $result = [];
        foreach ($files as $formKey => $file) {
            $destdir = $this->getRecordDirPath($datatype, $id);

            // create directory if it doesn't exist
            if (!file_exists($destdir)) {
                mkdir($destdir, $this->umask, true);
                chmod($destdir, $this->umask);
            }

            // upload
            if (isset($file->{'url'})) {
                $current = file_get_contents($file->{'url'});
                // get foobar.html from http://something.com/[...]/foobar.html
                $destfile = $destdir . '/' . basename($file->{'url'});

                if (file_put_contents($destfile, $current)) {
                    chmod($destfile, $this->umask);
                    $title = $file->{'title'};
                    $url = basename($file->{'url'});
                    $fileResult = $this->getFileResponse($destfile, $title, $url);
                    array_push($result, $fileResult);
                } else {
                    throw new \Exception($file['name'] . ' KO');
                }
            }
        }

        $response->setResult($result);
        $response->setCode(200);

        return $response;
    }

}
