<?php
declare(strict_types=1);

namespace App\Application\Actions;

use App\Application\Actions\Action;
use App\Domain\User\UserRepository;
use Psr\Log\LoggerInterface;
use App\Infrastructure\Services\ContentService;
use App\Infrastructure\Utils\Properties;
use App\Infrastructure\Rest\Response as RestResponse;

use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;//400
use Slim\Exception\HttpUnauthorizedException;//401
use Slim\Exception\HttpForbiddenException;//403
use Slim\Exception\HttpNotFoundException;//404
use Slim\Exception\HttpInternalServerErrorException;//500

abstract class RestAction extends Action
{




    /**
     * @param LoggerInterface $logger
     * @param UserRepository  $userRepository
     */
    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);
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
    * get conf
    * @return Properties  conf
    */
    public function getProperties()
    {
        return Properties::getInstance();
    }

    /**
     * replace it later
     */
    public function getParam(string $arg) : string
    {
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
    public function getRequestBody()
    {
        return $this->request->getParsedBody();
    }
    public function getRequestBodyStr() : string
    {
        return json_encode($this->request->getParsedBody());
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

    /**
     * @param  array|object|null $data
     * @return Response
     */
    protected function withResponse(RestResponse $resp)
    {
        // $this->slimException($request, $resp);
        return $this->respondWithData($resp->getResult(), $resp->getCode());
    }

    /*
        protected function slimException(ServerRequestInterface $request, RestResponse $response) {
            switch ($response->getCode()) {
                case 200:
                    break;
                case 400:
                  throw new HttpBadRequestException($request, $response->getError());
                  break;
                case 401:
                  throw new HttpUnauthorizedException($request, $response->getError());
                  break;
                case 403:
                    throw new HttpForbiddenException($request, $response->getError());
                  break;
                case 404:
                    throw new HttpNotFoundException($request, $response->getError());
                break;
                default:
                throw new HttpUnauthorizedException($request, $response->getError());;
              }
        }
        */
}
