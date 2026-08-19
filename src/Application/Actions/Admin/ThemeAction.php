<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin;

use App\Infrastructure\Utils\JsonUtils;
use Psr\Http\Message\ResponseInterface as Response;

class ThemeAction extends AdminAction
{
    protected function action(): Response
    {
        $this->checkConfiguration();

        $themeFile = $this->getPublicDirPath().'/theme/theme.json';
        if ($this->request->getMethod() === 'GET') {
            return $this->respondWithData(JsonUtils::readJsonFile($themeFile));
        }

        $data = $this->getFormDataOriginal();
        if (!is_object($data)) {
            throw new \InvalidArgumentException('Theme must be a JSON object');
        }

        JsonUtils::writeJsonFile($themeFile, $data);

        return $this->respondWithData($data);
    }
}