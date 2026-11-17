<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Infrastructure\Services\AuthService;
use App\Infrastructure\Services\ThrottleService;
use App\Infrastructure\Utils\MailUtils;
use App\Infrastructure\Utils\NetUtils;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Send a short-lived password reset link.
 */
class ResetPasswordAction extends AuthAction
{
    protected function action(): Response
    {
        $this->checkConfiguration();

        $resetUrl = $this->getProperties()->getString('passwordreseturl');
        if (!filter_var($resetUrl, FILTER_VALIDATE_URL)
            || !in_array(parse_url($resetUrl, PHP_URL_SCHEME), ['http', 'https'], true)
            || parse_url($resetUrl, PHP_URL_QUERY) !== null
            || parse_url($resetUrl, PHP_URL_FRAGMENT) !== null) {
            throw new \RuntimeException('A valid passwordreseturl must be configured');
        }

        $throttle = new ThrottleService($this->getPrivateDirPath().'/users');
        $retryAfter = $throttle->registerPasswordResetRequest(NetUtils::getClientIp());
        if ($retryAfter > 0) {
            $response = $this->getDefaultResponse();
            $response->setError(429, 'Too many password reset attempts');
            $error = $response->getResult();
            $error->{'retryAfter'} = $retryAfter;
            $response->setResult($error);

            return $this->withResponse($response);
        }

        $logindata = $this->getRequestBody();
        if (!isset($logindata->{'user'}) || !is_string($logindata->{'user'})) {
            $response = $this->getDefaultResponse();
            $response->setError(400, 'Missing user email');

            return $this->withResponse($response);
        }

        $email = $this->getUser($logindata);
        $service = new AuthService($this->getPrivateDirPath().'/users');
        $token = $service->requestPasswordReset($email);

        $response = $this->getDefaultResponse();
        $response->setCode(200);
        $response->setResult(new \stdClass());

        if ($token !== null) {
            $link = $resetUrl.'#user='.rawurlencode(strtolower(trim($email))).'&token='.rawurlencode($token);
            $mailUtils = new MailUtils($this->getRootDir());
            $notificationTitle = 'Password reset';
            $notificationBody = $mailUtils->getPasswordResetLink($notificationTitle, $link);
            $textBody = $mailUtils->getTextPasswordResetLink($notificationTitle, $link);
            $from = $mailUtils->getFrom($this->getConf()->{'mailsender'});

            if ($this->getProperties()->getBoolean('enablemail', true)) {
                $this->mail($from, $email, $email, $notificationTitle, $notificationBody, $textBody);
            }
        }

        unset($logindata);

        return $this->withResponse($response);
    }

    private function mail($from, $toAddress, $toName, $title, $htmlBody, $textBody): void
    {
        $mail = new PHPMailer();
        if ('true' === $this->getConf()->{'enablesmtp'}) {
            $mail->isSMTP();
            $mail->Host = $this->getConf()->{'smtphost'};
            $mail->SMTPAuth = true;
            $mail->Username = $this->getConf()->{'smtpusername'};
            $mail->Password = $this->getConf()->{'smtppassword'};
            $mail->SMTPSecure = $this->getConf()->{'smtpsecure'};
            $mail->Port = $this->getProperties()->getInteger('smtpport', 465);
        }

        $mail->setFrom($from, $from);
        $mail->addAddress($toAddress, $toName);
        $mail->Subject = $title;
        $mail->msgHTML($htmlBody);
        $mail->AltBody = $textBody;

        if (!$mail->send()) {
            error_log('Mailer Error: '.$mail->ErrorInfo);
        }
    }
}
