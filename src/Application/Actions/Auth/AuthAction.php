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



    /**
     * Get IP and user agent from client.
     *
     * @return string IP and user agent
     */
    public function getClientInfo(): string
    {
        $result = $this->getClientIp() . ' ';
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            // @codeCoverageIgnoreStart
            $result .= $_SERVER['HTTP_USER_AGENT'];
            // @codeCoverageIgnoreEnd
        }
        return $result;
    }

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
     * Get IP address.
     *
     * @return string IP address
     */
    public function getClientIp(): string
    {
        $ipaddress = '';
        // @codeCoverageIgnoreStart
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        // @codeCoverageIgnoreEnd
        } else {
            $ipaddress = 'UNKNOWN';
        }

        return $ipaddress;
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
