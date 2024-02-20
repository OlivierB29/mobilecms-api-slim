<?php

namespace App\Infrastructure\Utils;

class CaptchaUtils
{
    public static function captcha(): \stdClass
    {
        $result = \json_decode('{}');

        $val1 = random_int(10, 20);
        $val2 = random_int(1, 9);
        $calc = $val1 + $val2;

        $result->{'question'} = $val1.' + '.$val2.' = ?';
        $result->{'answer'} = ''.$calc;

        return $result;
    }
}
