<?php
declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Application\Actions\RestAction;
use App\Domain\User\UserRepository;
use Psr\Log\LoggerInterface;
use App\Infrastructure\Services\FileService;
use App\Infrastructure\Utils\Properties;
use App\Infrastructure\Rest\Response as RestResponse;
use App\Infrastructure\Services\AuthService;

abstract class AuthAction extends RestAction
{
    public function getUser($logindata): string
    {
        $result = null;
        if (isset($logindata->{'user'})) {
            $result = $logindata->{'user'};
        } else {
            if (isset($logindata->{'email'})) {
                $result = $logindata->{'email'};
            } else {
                // @codeCoverageIgnoreStart
                throw new \Exception('no user data');
                // @codeCoverageIgnoreEnd
            }
        }

        return $result;
    }



    /**
     * Check if directory is defined.
     */
    protected function checkConfiguration()
    {
        if (!isset($this->getConf()->{'privatedir'})) {
            throw new \Exception('Empty privatedir');
        }
    }
}
