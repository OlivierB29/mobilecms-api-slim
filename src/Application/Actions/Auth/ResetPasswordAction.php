<?php
declare(strict_types = 1);

namespace App\Application\Actions\Auth;

use App\Infrastructure\Services\AuthService;
use App\Infrastructure\Utils\MailUtils;
use App\Infrastructure\Utils\NetUtils;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Reset a password by sending an email with a new password
 */
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
            $from = $u->getFrom($this->getConf()->{'mailsender'});
            $date = date("Y-m-d H:i:s");
            $notificationBody = $u->getNewPassword('new password', $clearPassword, $this->getClientInfo(), $date);
            $textBody = $u->getNewTextPassword('new password', $clearPassword, $this->getClientInfo(), $date);

            //$notificationHeaders = $u->getHeaders($this->getConf()->{'mailsender'});

            if ($this->getProperties()->getBoolean('enablemail', true)) {
                // @codeCoverageIgnoreStart
                $this->mail($from, $email, $email, $notificationTitle, $notificationBody, $textBody);

            // @codeCoverageIgnoreEnd
            } elseif ($this->getProperties()->getBoolean('debugnotifications', false)) {
                $tmpResponse = $response->getResult();
                // test only
                $tmpResponse->{'notification'} = json_encode($textBody);
                $response->setResult($tmpResponse);
            } else {
                error_log("New password is: " . $clearPassword);
            }
        }

        unset($logindata);

        return $this->withResponse($response);
    }

    /**
     * Get IP and user agent from client.
     *
     * @return string IP and user agent
     */
    private function getClientInfo(): string
    {
        $result = NetUtils::getClientIp() . ' ';
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            // @codeCoverageIgnoreStart
            $result .= $_SERVER['HTTP_USER_AGENT'];
            // @codeCoverageIgnoreEnd
        }
        return $result;
    }

    private function mail($from, $toAddress, $toName, $title, $htmlBody, $textBody)
    {
        //Create a new PHPMailer instance
        $mail = new PHPMailer();
        if ('true' === $this->getConf()->{'enablesmtp'}) {
            $mail->isSMTP(); // use smtp
            $mail->Host = $this->getConf()->{'smtphost'}; // host
            $mail->SMTPAuth = true; // auth
            $mail->Username = $this->getConf()->{'smtpusername'}; // username
            $mail->Password = $this->getConf()->{'smtppassword'}; // password
            $mail->SMTPSecure = $this->getConf()->{'smtpsecure'}; // SSL
            $mail->Port = $this->getProperties()->getInteger('smtpport', 465);
        }

        //Set who the message is to be sent from
        $mail->setFrom($from, $from);

        //Set who the message is to be sent to
        $mail->addAddress($toAddress, $toName);
        //Set the subject line
        $mail->Subject = $title;
        //Read an HTML message body from an external file, convert referenced images to embedded,
        //convert HTML into a basic plain-text alternative body
        $mail->msgHTML($htmlBody);
        //Replace the plain text body with one created manually
        $mail->AltBody = $textBody;
        //Attach an image file
        //$mail->addAttachment('images/phpmailer_mini.png');

        //send the message, check for errors
        if (!$mail->send()) {
            error_log('Mailer Error: ' . $mail->ErrorInfo);
        }
    }
}
