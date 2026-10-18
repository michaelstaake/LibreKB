<?php

/**
 * Per-session token for state-changing requests.
 */
class Csrf
{
    const SESSION_KEY = '_csrf_token';
    const FIELD = 'csrf_token';

    public static function token()
    {
        if (empty($_SESSION[self::SESSION_KEY]) || !is_string($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::SESSION_KEY];
    }

    public static function field()
    {
        return '<input type="hidden" name="' . self::FIELD . '" value="'
            . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function validate()
    {
        $sent = $_POST[self::FIELD] ?? '';
        $expected = $_SESSION[self::SESSION_KEY] ?? '';
        if (!is_string($sent) || $sent === '' || !is_string($expected) || $expected === '') {
            return false;
        }
        return hash_equals($expected, $sent);
    }
}
