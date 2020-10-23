<?php
declare(strict_types=1);

namespace App\Application\Actions;


use Psr\Http\Message\ResponseInterface as Response;
use App\Infrastructure\Services\FileService;

use App\Infrastructure\Utils\FileUtils;

class DefaultOptionsAction extends AuthAction
{

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
 
                return $this->respondWithData(\json_decode('{}'));
    }
}
