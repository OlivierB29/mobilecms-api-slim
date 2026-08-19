<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin;

use App\Infrastructure\Utils\JsonUtils;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class ThemeBannerUploadAction extends AdminAction
{
    protected function action(): Response
    {
        $this->checkConfiguration();

        $uploadedFiles = $this->request->getUploadedFiles();
        $file = $uploadedFiles['banner'] ?? null;
        if ($file === null || $file->getError() !== UPLOAD_ERR_OK) {
            throw new HttpBadRequestException($this->request, 'No banner file uploaded.');
        }

        $filename = basename((string) $file->getClientFilename());
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if ($filename === '' || !in_array($extension, $allowedExtensions, true)) {
            throw new HttpBadRequestException($this->request, 'Forbidden banner file type.');
        }

        $directory = $this->getMediaDirPath().'/theme/banner';
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException('Unable to create banner directory.');
        }

        $file->moveTo($directory.'/'.$filename);
        $imageUrl = 'media/theme/banner/'.$filename;
        $themeFile = $this->getPublicDirPath().'/theme/theme.json';
        $theme = JsonUtils::readJsonFile($themeFile);
        if (!is_object($theme)) {
            throw new \RuntimeException('Theme file must contain a JSON object.');
        }
        if (!isset($theme->banner) || !is_object($theme->banner)) {
            $theme->banner = new \stdClass();
        }
        $theme->banner->imageurl = $imageUrl;
        JsonUtils::writeJsonFile($themeFile, $theme);

        return $this->respondWithData((object) [
            'url' => $imageUrl,
            'title' => $filename
        ]);
    }
}