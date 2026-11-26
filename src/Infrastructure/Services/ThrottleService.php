<?php

namespace App\Infrastructure\Services;

use App\Infrastructure\Utils\JsonUtils;
use App\Infrastructure\Utils\NetUtils;

/**
 * Control failed logins.
 */
class ThrottleService
{
    private const PASSWORD_RESET_LIMIT = 5;

    private const PASSWORD_RESET_WINDOW_SECONDS = 120;

    private const LOCKOUTS = [
        3  => 60,
        10 => 300,
        20 => 1800,
    ];

    /**
     * database directory.
     */
    private $databasedir;

    /**
     * Constructor.
     *
     * @param string $databasedir eg : public
     */
    public function __construct(string $databasedir)
    {
        $this->databasedir = $databasedir;
    }

    public function getLoginHistoryFileName(string $user, ?string $ip = null)
    {
        if ($ip !== null) {
            return $this->databasedir.'/'.'history'.'/'.hash('sha256', strtolower($user).'|'.$ip).'.json';
        }

        return $this->databasedir.'/'.'history'.'/'.$user.'.json';
    }

    public function getRetryAfter(string $user, string $ip): int
    {
        $failedList = $this->getFailedLoginList($user, $ip);
        $count = count($failedList);
        $duration = 0;

        foreach (self::LOCKOUTS as $threshold => $seconds) {
            if ($count >= $threshold) {
                $duration = $seconds;
            }
        }

        if ($duration === 0 || $count === 0) {
            return 0;
        }

        $lastFailure = $failedList[$count - 1];
        $retryAfter = ((int) $lastFailure->{'timestamp'}) + $duration - time();

        return max(0, $retryAfter);
    }

    public function recordFailedLogin(string $user, string $ip): int
    {
        $file = $this->getLoginHistoryFileName($user, $ip);
        $history = $this->readHistory($file);
        $failedList = $this->getFailedList($history);

        $failedList[] = $this->createFailedLoginRecord($user, $ip);
        $history->{'failed'} = $failedList;
        JsonUtils::writeJsonFile($file, $history);

        return count($failedList);
    }

    public function clearFailedLogins(string $user, string $ip): void
    {
        $file = $this->getLoginHistoryFileName($user, $ip);
        if (file_exists($file)) {
            JsonUtils::writeJsonFile($file, json_decode('{"failed":[]}'));
        }
    }

    public function registerPasswordResetRequest(string $ip): int
    {
        $file = $this->databasedir.'/'.'history'.'/reset-'.hash('sha256', $ip).'.json';
        $directory = dirname($file);
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new \RuntimeException('Unable to create throttle directory '.$directory);
        }

        $handle = fopen($file, 'c+');
        if ($handle === false) {
            throw new \RuntimeException('Unable to open password reset throttle file '.$file);
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                throw new \RuntimeException('Unable to lock password reset throttle file '.$file);
            }

            $contents = stream_get_contents($handle);
            if ($contents === false) {
                throw new \RuntimeException('Unable to read password reset throttle file '.$file);
            }
            $timestamps = $contents === '' ? [] : json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($timestamps) || !array_is_list($timestamps)) {
                throw new \RuntimeException('Invalid password reset throttle data in '.$file);
            }
            foreach ($timestamps as $timestamp) {
                if (!is_int($timestamp)) {
                    throw new \RuntimeException('Invalid password reset timestamp in '.$file);
                }
            }

            $now = time();
            $timestamps = array_values(array_filter($timestamps, static function (int $timestamp) use ($now): bool {
                return $timestamp > $now - self::PASSWORD_RESET_WINDOW_SECONDS && $timestamp <= $now;
            }));

            if (count($timestamps) >= self::PASSWORD_RESET_LIMIT) {
                return max(1, $timestamps[0] + self::PASSWORD_RESET_WINDOW_SECONDS - $now);
            }

            $timestamps[] = $now;
            $encoded = json_encode($timestamps, JSON_THROW_ON_ERROR);
            if (!rewind($handle) || !ftruncate($handle, 0) || fwrite($handle, $encoded) !== strlen($encoded) || !fflush($handle)) {
                throw new \RuntimeException('Unable to write password reset throttle file '.$file);
            }

            return 0;
        } finally {
            if (is_resource($handle)) {
                flock($handle, LOCK_UN);
                fclose($handle);
            }
        }
    }

    public function saveFailedLogin(string $user)
    {
        $result = -1;
        // file name
        $file = $this->getLoginHistoryFileName($user);

        $history = null;
        $failedList = null;
        // TODO add failed login
        if (\file_exists($file)) {
            $history = JsonUtils::readJsonFile($file);
            $failedList = isset($history->{'failed'}) && is_array($history->{'failed'})
                ? $history->{'failed'}
                : [];
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
            $failedList = isset($history->{'failed'}) && is_array($history->{'failed'})
                ? $history->{'failed'}
                : [];
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
            $failedList = isset($history->{'failed'}) && is_array($history->{'failed'})
                ? $history->{'failed'}
                : [];
        } else {
            $history = \json_decode('{}');
            $failedList = [];
        }

        $history->{'failed'} = [];
        if (\count($failedList) === 0) {
            $history->{'archive'.date('YmdHis')} = $failedList;
        }

        $result = count($failedList);
        // write to file
        JsonUtils::writeJsonFile($file, $history);

        return $result;
    }

    public function createFailedLoginRecord(string $user, ?string $ip = null)
    {
        $result = \json_decode('{}');
        $result->{'date'} = date('D M d Y G:i');
        $result->{'timestamp'} = time();
        $result->{'ip'} = $ip ?? NetUtils::getClientIp();

        return $result;
    }

    private function getFailedLoginList(string $user, string $ip): array
    {
        return $this->getFailedList($this->readHistory($this->getLoginHistoryFileName($user, $ip)));
    }

    private function readHistory(string $file): \stdClass
    {
        if (!file_exists($file)) {
            return json_decode('{}');
        }

        $history = JsonUtils::readJsonFile($file);

        return $history instanceof \stdClass ? $history : json_decode('{}');
    }

    private function getFailedList(\stdClass $history): array
    {
        if (!isset($history->{'failed'}) || !is_array($history->{'failed'})) {
            return [];
        }

        return array_values(array_filter($history->{'failed'}, function ($failed) {
            return isset($failed->{'timestamp'});
        }));
    }
}
