<?php

namespace App\Infrastructure\Utils;

define('EMPTYSTR', '');
/**
 * Java-like App\Infrastructure\Utils\StringUtils.
 */
class StringUtils
{
    /**
     * Starts with string ?
     *
     * @param string $haystack eg "foobar"
     * @param string $needle   eg "foo"
     *
     * @return bool result
     */
    public static function startsWith(string $haystack, string $needle)
    {
        $length = strlen($needle);

        return substr($haystack, 0, $length) === $needle;
    }

    /**
     * Ends with string ?
     *
     * @param string $haystack eg "foobar"
     * @param string $needle   eg "bar"
     *
     * @return bool result
     */
    public static function endsWith(string $haystack, string $needle)
    {
        $length = strlen($needle);

        return substr($haystack, -$length) === $needle;
    }
}
