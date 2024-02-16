<?php namespace App\Infrastructure\Services;

use App\Infrastructure\Utils\CaptchaUtils;
use App\Infrastructure\Utils\JsonUtils;
use App\Infrastructure\Utils\NetUtils;

/**
 * Control failed logins and check captcha
 */
class ThrottleService
{
    /**
     * database directory.
     */
    private $databasedir;

    private $maxfailed = 5;
    /**
     * Constructor.
     *
     * @param string $databasedir eg : public
     */
    public function __construct(string $databasedir)
    {
        $this->databasedir = $databasedir;
    }

    public function getLoginHistoryFileName(string $user)
    {
        return $this->databasedir . '/' . 'history' . '/' . $user . '.json';
    }

    public function getCaptchaFileName(string $user)
    {
        return $this->databasedir . '/' . 'captcha' . '/' . $user . '.json';
    }

    public function saveFailedLogin(string $user)
    {
        $result = -1;
        // file name
        $file = $this->getLoginHistoryFileName($user);

        $history = null;
        $failedList = null;
        // TODO add failed login
        if (file_exists($file)) {
            $history = JsonUtils::readJsonFile($file);
            $failedList = $history->{'failed'};
        } else {
            $history = \json_decode('{}');
            $failedList = [];
        }

        $failed = $this->createFailedLoginRecord($user);

        \array_push($failedList, $failed);
        $history->{'failed'} = $failedList;
        $result = count($failedList);
        // write to file
        JsonUtils::writeJsonFile($file, $history);

        return $result;
    }

    public function countFailedLogin(string $user)
    {
        $result = 0;

        // file name
        $file = $this->getLoginHistoryFileName($user);

        // TODO add failed login
        if (file_exists($file)) {
            $history = JsonUtils::readJsonFile($file);
            $failedList = $history->{'failed'};
            $result = count($failedList);
        }

        return $result;
    }

    public function archiveOldFailed(string $user)
    {
        $result = -1;
        // file name
        $file = $this->getLoginHistoryFileName($user);

        $history = null;
        $failedList = null;
        // TODO add failed login
        if (file_exists($file)) {
            $history = JsonUtils::readJsonFile($file);
            $failedList = $history->{'failed'};
        } else {
            $history = \json_decode('{}');
            $failedList = [];
        }

        if (\file_exists($this->getCaptchaFileName($user))) {
            \unlink($this->getCaptchaFileName($user));
        }

        $history->{'failed'} = [];
        $history->{'archive' . date("YmdHis")} = $failedList;
        $result = count($failedList);
        // write to file
        JsonUtils::writeJsonFile($file, $history);

        return $result;
    }

    public function isCaptchaRequired(string $user)
    {
        $result = false;

        $failed = $this->countFailedLogin($user);

        if ($failed >= $this->maxfailed) {
            $result = true;
        }

        return $result;
    }

    public function getCaptcha(string $user)
    {
        $result = null;

        $failed = $this->countFailedLogin($user);

        if ($failed >= $this->maxfailed) {
            $file = $this->getCaptchaFileName($user);
            $result = JsonUtils::readJsonFile($file);
        }

        return $result;
    }

    public function createCaptcha(string $user)
    {
        $result = null;

        $result = CaptchaUtils::captcha();

        $file = $this->getCaptchaFileName($user);
        JsonUtils::writeJsonFile($file, $result);
        

        return $result;
    }

    public function verifyCaptcha(string $user, string $answer): bool
    {
        $result = false;
        $file = $this->getCaptchaFileName($user);
        $captchaVerify = JsonUtils::readJsonFile($file);
        if ($captchaVerify->{'answer'} === $answer) {
            $result = true;
        }

        return $result;
    }

    public function createFailedLoginRecord(string $user)
    {
        $result = \json_decode('{}');
        $result->{'date'} = date("D M d Y G:i");
        $result->{'ip'} = NetUtils::getClientIp();
        return $result;
    }
}
