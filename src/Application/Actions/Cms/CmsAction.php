<?php
declare(strict_types=1);

namespace App\Application\Actions\Cms;

use App\Application\Actions\RestAction;

use App\Infrastructure\Services\ContentService;

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
