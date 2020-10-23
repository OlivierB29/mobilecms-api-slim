<?php
declare(strict_types=1);

namespace App\Application\Actions\Cms;

use App\Application\Actions\RestAction;
use App\Domain\User\UserRepository;
use Psr\Log\LoggerInterface;
use App\Infrastructure\Services\ContentService;
use App\Infrastructure\Utils\Properties;
use App\Infrastructure\Rest\Response as RestResponse;

abstract class CmsAction extends RestAction
{

    /*
    * reserved id column
    */
    const ID = 'id';




    private $service;



            /**
     * Get a service
     */
    protected function getService(): ContentService
    {
        if ($this->service == null) {
            $this->service = new ContentService($this->getPublicDirPath());
        }
        
        return $this->service;
    }



}
