<?php
declare(strict_types=1);

namespace App\Application\Actions;


use Psr\Http\Message\ResponseInterface as Response;
use App\Infrastructure\Services\FileService;



class DefaultOptionsAction extends Action
{

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
 
                return $this->respondWithData(\json_decode('{}'));
    }
}
