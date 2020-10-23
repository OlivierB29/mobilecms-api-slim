<?php
declare(strict_types=1);

namespace App\Application\Actions\File;


use Psr\Http\Message\ResponseInterface as Response;
use App\Infrastructure\Services\FileService;

use App\Infrastructure\Utils\FileUtils;

class DeleteFilesAction extends FileAction
{

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $response = $this->getDefaultResponse();

        $files = json_decode($filesStr);

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

        $response->setResult(json_encode($result));
        $response->setCode(200);


                return $this->response($this->request, $response);
    }
}
