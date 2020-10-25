<?php
declare(strict_types=1);

namespace App\Application\Actions\Auth;


use Psr\Http\Message\ResponseInterface as Response;
use App\Infrastructure\Services\FileService;
use App\Infrastructure\Utils\MailUtils;
use App\Infrastructure\Utils\FileUtils;
use App\Infrastructure\Services\AuthService;
class ResetPasswordAction extends AuthAction
{

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $response = $this->getDefaultResponse();

        //throw error if wrong configuration, such as empty directory
        $this->checkConfiguration();

        $service = new AuthService($this->getPrivateDirPath() . '/users');


            // login and get token
            // eg : { "user": "test@example.com", "password":"Sample#123456"}

            $logindata = $this->getRequestBody();

            //TODO : user contains either email of name

            // free variables before response
            $clearPassword = $service->generateRandomString(20);

            $response = $service->resetPassword($this->getUser($logindata), $clearPassword);

            if ($response->getCode() === 200) {
                $u = new MailUtils($this->getRootDir());

                $email = $this->getUser($logindata);
                $notificationTitle = 'new password';
                $notificationBody = $u->getNewPassword('new password', $clearPassword, $this->getClientInfo());
                $notificationHeaders = $u->getHeaders($this->getConf()->{'mailsender'});

                if ($this->getProperties()->getBoolean('enablemail', true)) {
                    // @codeCoverageIgnoreStart
                    $CR_Mail = @mail(
                        $email,
                        'new password',
                        $notificationBody,
                        $notificationHeaders
                    );

                    if ($CR_Mail === false) {
                        $response->setError(500, $CR_Mail);
                    } else {
                        $response->setCode(200);
                    }
                    // @codeCoverageIgnoreEnd
                } elseif ($this->getProperties()->getBoolean('debugnotifications', true)) {
                    $tmpResponse = $response->getResult();
                    // test only
                    $tmpResponse->{'notification'} = json_encode($notificationBody);
                    $response->setResult($tmpResponse);
                }
            }

            unset($logindata);
        
                return $this->withResponse( $response);
    }

        /**
     * Get IP and user agent from client.
     *
     * @return string IP and user agent
     */
    private function getClientInfo(): string
    {
        $result = $this->getClientIp() . ' ';
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            // @codeCoverageIgnoreStart
            $result .= $_SERVER['HTTP_USER_AGENT'];
            // @codeCoverageIgnoreEnd
        }
        return $result;
    }


    /**
     * Get IP address.
     *
     * @return string IP address
     */
    private function getClientIp(): string
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

}
