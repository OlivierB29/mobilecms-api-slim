<?php
declare(strict_types=1);

namespace App\Application\Actions\Cms;

use App\Application\Actions\Action;
use App\Domain\User\UserRepository;
use Psr\Log\LoggerInterface;
use App\Infrastructure\Services\ContentService;
use App\Infrastructure\Utils\Properties;
use App\Infrastructure\Rest\Response as RestResponse;

abstract class CmsAction extends Action
{

    /*
    * reserved id column
    */
    const ID = 'id';


    /**
    * configuration
    */
    protected $properties ;

    private $service;

    /**
     * @param LoggerInterface $logger
     * @param UserRepository  $userRepository
     */
    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);

    }

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



    /**
     * Get main working directory.
     *
     * @return string rootDir main working directory
     */
    public function getRootDir(): string
    {
        return Properties::getInstance()->getRootDir();
    }

    /**
     * Get public directory.
     *
     * @return string publicdir main public directory
     */
    public function getPublicDirPath(): string
    {
        return $this->getRootDir() . $this->getConf()->{'publicdir'};
    }

    /**
     * Get public directory.
     *
     * @return string publicdir main public directory
     */
    public function getMediaDirPath(): string
    {
        return $this->getRootDir() . $this->getConf()->{'media'};
    }

    /**
     * Get privatedir directory.
     *
     * @return string privatedir main privatedir directory
     */
    public function getPrivateDirPath(): string
    {
        return $this->getRootDir() . $this->getConf()->{'privatedir'};
    }

    /**
    * get JSON conf
    * @return \stdClass JSON conf
    */
    public function getConf()
    {
        return Properties::getInstance()->getConf();
    }

    /**
     * replace it later
     */
    public function getParam(string $arg) : string {
        return $this->resolveArg($arg);
    }
    
  
    /**
     * replace it later
     */  
    /*
    public function getRequestBody() : mixed {
        return $this->getFormData();
    }
*/
    public function getRequestBody() : string {
        return $this->getFormData()->__toString();
    }

        /**
     * Initialize a default Response object.
     *
     * @return Response object
     */
    protected function getDefaultResponse() : RestResponse
    {
        $response = new RestResponse();
        $response->setCode(400);
        $response->setResult(new \stdClass);

        return $response;
    }
}
