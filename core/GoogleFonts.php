<?php

/**
 * Checks a Google Fonts stylesheet URL before it is stored and again before it is printed.
 * An empty value means the public site does not load a Google Font.
 */
class GoogleFonts
{
    const MAX_LENGTH = 2000;

    public static function rejectionMessage($url)
    {
        $url = trim((string)$url);
        if ($url === '') {
            return '';
        }
        if (!self::isAllowed($url)) {
            return 'Google Fonts CSS URL must be an https address on fonts.googleapis.com with no spaces or quotes, such as https://fonts.googleapis.com/css2?family=Roboto&display=swap.';
        }
        if (self::length($url) > self::MAX_LENGTH) {
            return 'Google Fonts CSS URL must be 2,000 characters or fewer.';
        }
        return '';
    }

    public static function printable($url)
    {
        $url = trim((string)$url);
        if ($url === '' || self::rejectionMessage($url) !== '') {
            return '';
        }
        return $url;
    }

    public static function length($url)
    {
        $url = (string)$url;
        if (function_exists('mb_strlen')) {
            return mb_strlen($url, 'UTF-8');
        }
        return strlen($url);
    }

    private static function isAllowed($url)
    {
        if (preg_match('/[\s\'"<>\\\\]/', $url) === 1 || strpos($url, "\0") !== false) {
            return false;
        }
        if (preg_match('/[\x00-\x1F\x7F]/', $url) === 1) {
            return false;
        }
        if (preg_match('/\A[A-Za-z0-9:\/?&=+;,@._%|-]+\z/', $url) !== 1) {
            return false;
        }
        if (preg_match('/%(?:00|0[ad]|09|5c)/i', $url) === 1) {
            return false;
        }
        if (preg_match('/\Ahttps:\/\/fonts\.googleapis\.com\/(?:css2|css)\?/i', $url) !== 1) {
            return false;
        }

        $parts = parse_url($url);
        if (!is_array($parts)) {
            return false;
        }
        if (!isset($parts['scheme'], $parts['host'], $parts['path'], $parts['query'])) {
            return false;
        }
        if (strtolower((string)$parts['scheme']) !== 'https') {
            return false;
        }
        if (strtolower((string)$parts['host']) !== 'fonts.googleapis.com') {
            return false;
        }
        if (isset($parts['user']) || isset($parts['pass']) || isset($parts['port']) || isset($parts['fragment'])) {
            return false;
        }
        if ($parts['path'] !== '/css' && $parts['path'] !== '/css2') {
            return false;
        }
        if (strpos((string)$parts['path'], '..') !== false) {
            return false;
        }

        return self::hasFamily((string)$parts['query']);
    }

    private static function hasFamily($query)
    {
        $params = [];
        parse_str($query, $params);
        if (!isset($params['family'])) {
            return false;
        }

        $families = $params['family'];
        if (!is_array($families)) {
            $families = [$families];
        }
        if ($families === []) {
            return false;
        }

        foreach ($families as $family) {
            if (!is_string($family) || trim($family) === '') {
                return false;
            }
        }

        return true;
    }
}
