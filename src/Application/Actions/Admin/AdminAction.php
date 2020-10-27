<?php
declare(strict_types=1);

namespace App\Application\Actions\Admin;

use App\Application\Actions\RestAction;
use App\Domain\User\UserRepository;
use Psr\Log\LoggerInterface;
use App\Infrastructure\Services\FileService;
use App\Infrastructure\Services\ContentService;
use App\Infrastructure\Utils\Properties;
use App\Infrastructure\Rest\Response;

abstract class AdminAction extends RestAction
{
    const INDEX_JSON = '/index/index.json';


    protected $role = 'admin';


    /**
     * Basic user fields, without password.
     *
     * @param \stdClass $user JSON user
     *
     * @return \stdClass JSON user without password
     */
    public function getUserResponse(\stdClass $user): \stdClass
    {
        $responseUser = json_decode('{}');
        $responseUser->{'name'} = $user->{'name'};
        $responseUser->{'email'} = $user->{'email'};
        $responseUser->{'role'} = $user->{'role'};

        return $responseUser;
    }






    /**
     * Initialize a default user object.
     *
     * *@return \stdClass user JSON object
     */
    protected function getDefaultUser(): \stdClass
    {
        return json_decode('{"name":"", "email":"", "password":"" }');
    }


    /**
     * Check config and throw an exception if needed.
     */
    protected function checkConfiguration()
    {
        if (!isset($this->getConf()->{'privatedir'})) {
            // @codeCoverageIgnoreStart
            throw new \Exception('Empty privatedir');
            // @codeCoverageIgnoreEnd
        }
    }
}
