<?php

/**
 * Checks custom CSS before it is stored and again before it is printed.
 */
class CustomCss
{
    const MAX_LENGTH = 20000;

    public static function rejectionMessage($css)
    {
        $css = (string)$css;
        if (self::containsForbidden($css)) {
            return 'Custom CSS cannot contain a less-than character, a null byte, @import, expression(, javascript:, vbscript:, -moz-binding, or behavior:.';
        }
        if (self::length($css) > self::MAX_LENGTH) {
            return 'Custom CSS must be 20,000 characters or fewer.';
        }
        return '';
    }

    public static function printable($css)
    {
        $css = (string)$css;
        if ($css === '' || self::rejectionMessage($css) !== '') {
            return '';
        }
        return $css;
    }

    public static function length($css)
    {
        $css = (string)$css;
        if (function_exists('mb_strlen')) {
            return mb_strlen($css, 'UTF-8');
        }
        return strlen($css);
    }

    private static function containsForbidden($css)
    {
        if (strpos($css, '<') !== false || strpos($css, "\0") !== false) {
            return true;
        }

        return preg_match('/@import|expression\s*\(|javascript\s*:|vbscript\s*:|-moz-binding|behavior\s*:/i', $css) === 1;
    }
}
