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

    /**
     * Convert a value to a filename-safe slug.
     * eg: "Été kendo 2026" -> "ete-kendo-2026"
     */
    public static function slugify(string $value): string
    {
        $value = trim($value);
        if ($value === EMPTYSTR) {
            return EMPTYSTR;
        }

        if (function_exists('iconv')) {
            $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
            if ($transliterated !== false) {
                $value = $transliterated;
            }
        }

        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value);
        if ($value === null) {
            return EMPTYSTR;
        }

        return trim($value, '-');
    }
}
