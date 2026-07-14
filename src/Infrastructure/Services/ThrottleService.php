<?php

namespace App\Infrastructure\Services;


use App\Infrastructure\Utils\JsonUtils;
use App\Infrastructure\Utils\NetUtils;

/**
 * Control failed logins
 */
class ThrottleService
{
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

    public function getLoginHistoryFileName(string $user)
    {
        return $this->databasedir.'/'.'history'.'/'.$user.'.json';
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


        $history->{'failed'} = [];
        if (\count($failedList) === 0) {
            $history->{'archive'.date('YmdHis')} = $failedList;
        }

        $result = count($failedList);
        // write to file
        JsonUtils::writeJsonFile($file, $history);

        return $result;
    }


   


    public function createFailedLoginRecord(string $user)
    {
        $result = \json_decode('{}');
        $result->{'date'} = date('D M d Y G:i');
        $result->{'ip'} = NetUtils::getClientIp();

        return $result;
    }
}
