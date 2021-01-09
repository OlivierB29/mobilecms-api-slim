<?php namespace App\Infrastructure\Utils;

use App\ApiConstants;

/**
 * Mail Utility.
 */
class MailUtils
{
    /**
     * eg : /var/www/html.
     */
    private $rootdir;

    /**
     * Constructor.
     */
    public function __construct(string $rootdir)
    {
        $this->rootdir = $rootdir;
    }



    /**
     * Generate new password. Should separate technical functions and business.
     *
     * @param string $subject    mail subject
     * @param string $password   new password
     * @param string $clientinfo client data (IP, browser, ...)
     *
     * @return string notification content
     */
    public function getNewPassword(string $subject, string $password, string $clientinfo) : string
    {
        $message = file_get_contents($this->rootdir . '/api' . ApiConstants::VERSION . '/mail/newpassword.html');
        $message = str_replace('%subject%', $subject, $message);
        $message = str_replace('%password%', $password, $message);
        $message = str_replace('%clientinfo%', $clientinfo, $message);

        return $message;
    }

    /**
     * @param string $from mail address
     *
     * @return string mail headers
     */
    public function getHeaders(string $from) : string
    {
        if (empty($from)) {
            // @codeCoverageIgnoreStart
            $from = 'no-reply@' . $_SERVER['HTTP_HOST'];
            // @codeCoverageIgnoreEnd
        }
        $name = $from;

        $headers = 'From: ' . $name . '<' . $from . '>' . "\r\n";
        $headers  .= 'Reply-To: ' . $from . "\r\n";
        $headers  .= 'MIME-Version: 1.0' . "\r\n";
        $headers  .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";


        return $headers;
    }
}
