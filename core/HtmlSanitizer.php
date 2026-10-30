<?php

/**
 * Allowlist cleaner for article HTML.
 *
 * TinyMCE formatting is kept. Scripts, event handlers, and dangerous URLs
 * are removed on save and again whenever an article is rendered. Internal
 * article links are rewritten so a subdirectory install keeps its base path.
 */
class HtmlSanitizer
{
    private static $purifier = null;
    private static $install = false;

    public static function clean($html)
    {
        $html = (string)$html;
        if ($html === '') {
            return '';
        }

        try {
            $clean = self::purifier()->purify($html);
        } catch (Throwable $e) {
            // A cleaner failure must not fall back to raw HTML.
            return htmlspecialchars($html, ENT_QUOTES, 'UTF-8');
        }

        return self::normalizeInternalLinks($clean);
    }

    private static function purifier()
    {
        if (self::$purifier instanceof HTMLPurifier) {
            return self::$purifier;
        }

        $library = ROOT_PATH . '/vendor/htmlpurifier/library/HTMLPurifier.auto.php';
        if (!class_exists('HTMLPurifier', false)) {
            require_once $library;
        }

        $config = HTMLPurifier_Config::createDefault();
        $config->set('Core.Encoding', 'UTF-8');
        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
        $config->set('HTML.Allowed', implode(',', array(
            'h1[style]',
            'h2[style]',
            'h3[style]',
            'h4[style]',
            'h5[style]',
            'h6[style]',
            'p[style]',
            'br',
            'hr',
            'div[style]',
            'span[style]',
            'blockquote[style]',
            'strong',
            'b',
            'em',
            'i',
            'u',
            's',
            'strike',
            'sub',
            'sup',
            'code',
            'pre',
            'ul',
            'ol',
            'li',
            'a[href|title|target|rel]',
            'img[src|alt|width|height|title]',
            'table[border|cellpadding|cellspacing|width|style]',
            'thead',
            'tbody',
            'tfoot',
            'tr[style]',
            'th[colspan|rowspan|scope|abbr|style|width|align|valign]',
            'td[colspan|rowspan|style|width|align|valign]',
            'caption',
            'colgroup',
            'col[span|width]'
        )));
        $config->set('CSS.AllowedProperties', array(
            'text-align' => true,
            'text-decoration' => true,
            'font-size' => true,
            'font-weight' => true,
            'font-style' => true,
            'color' => true,
            'background-color' => true,
            'width' => true,
            'height' => true,
            'border' => true,
            'border-collapse' => true,
            'border-width' => true,
            'border-style' => true,
            'border-color' => true,
            'padding' => true,
            'padding-left' => true,
            'padding-right' => true,
            'padding-top' => true,
            'padding-bottom' => true,
            'vertical-align' => true,
            'white-space' => true,
            'list-style-type' => true
        ));
        $config->set('Attr.AllowedFrameTargets', array('_blank' => true));
        $config->set('HTML.TargetNoreferrer', true);

        $schemes = array(
            'http' => true,
            'https' => true,
            'mailto' => true
        );
        // data: is limited by HTML Purifier to GIF, JPEG, and PNG, and the
        // bytes are checked with exif_imagetype or getimagesize.
        if (function_exists('exif_imagetype') || function_exists('getimagesize')) {
            $schemes['data'] = true;
        }
        $config->set('URI.AllowedSchemes', $schemes);
        $config->set('URI.DisableExternalResources', false);

        $cache = ROOT_PATH . '/storage/cache/htmlpurifier';
        if (!is_dir($cache)) {
            @mkdir($cache, 0775, true);
        }
        if (is_dir($cache) && is_writable($cache)) {
            $config->set('Cache.SerializerPath', $cache);
        } else {
            $config->set('Cache.DefinitionImpl', null);
        }

        self::$purifier = new HTMLPurifier($config);
        return self::$purifier;
    }

    /**
     * TinyMCE turns a pasted same-site URL into a path relative to the article
     * editor (/admin/articles/...). That page sits one folder deeper than a
     * public article, so ../../article/slug drops the install base and the
     * public page requests /article/slug. Rebuild those editor-relative links
     * as root-relative paths that still include the base. Absolute URLs,
     * root-relative paths under the base, and a bare slug are left as stored:
     * the first two already contain the base, and the public article page
     * resolves a slug by itself.
     */
    private static function normalizeInternalLinks($html)
    {
        if (stripos($html, '<a') === false) {
            return $html;
        }

        $install = self::installLocation();
        if ($install === null) {
            return $html;
        }

        $rewritten = preg_replace_callback(
            '/<a\b[^>]*?\bhref\s*=\s*(["\'])([^"\']*)\1/i',
            function ($matches) use ($install) {
                $decoded = html_entity_decode($matches[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $normalized = self::normalizeHref($decoded, $install);
                if ($normalized === null || $normalized === $decoded) {
                    return $matches[0];
                }
                $encoded = htmlspecialchars($normalized, ENT_QUOTES, 'UTF-8');
                return substr($matches[0], 0, -strlen($matches[1] . $matches[2] . $matches[1]))
                    . $matches[1] . $encoded . $matches[1];
            },
            $html
        );

        return is_string($rewritten) ? $rewritten : $html;
    }

    private static function normalizeHref($href, $install)
    {
        $href = trim($href);
        if ($href === '' || $href[0] === '#' || strlen($href) > 2000) {
            return null;
        }
        if (preg_match('/[\s\x00-\x1F\x7F\\\\]/', $href) === 1) {
            return null;
        }
        if (preg_match('/\A(?:mailto|javascript|data|tel|sms):/i', $href) === 1) {
            return null;
        }
        // Absolute and protocol-relative URLs already carry their path.
        // Root-relative paths do too. Only a path with no leading slash can
        // be the editor-relative form that lost the base.
        if (preg_match('/\A[a-z][a-z0-9+.-]*:/i', $href) === 1 || $href[0] === '/') {
            return null;
        }

        list($path, $query, $fragment) = self::splitHref($href);
        if ($path === '' || strpos($path, '..') === false) {
            return null;
        }

        $resolved = self::resolveAgainstEditor($path, $install['basePath']);
        if ($resolved === null) {
            return null;
        }
        $canonical = self::canonicalPublicPath($resolved, $install['basePath']);
        if ($canonical === null) {
            return null;
        }

        return self::formatUrl($canonical, $query, $fragment);
    }

    /**
     * systemURL is the public address, including a subfolder when there is one.
     */
    private static function installLocation()
    {
        if (self::$install !== false) {
            return self::$install;
        }

        self::$install = null;
        if (!class_exists('Config', false)) {
            return null;
        }

        try {
            $config = new Config();
        } catch (Throwable $e) {
            return null;
        }

        $systemURL = isset($config->systemURL) ? trim((string)$config->systemURL) : '';
        if ($systemURL === '') {
            return null;
        }

        $parts = parse_url($systemURL);
        if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
            return null;
        }
        $scheme = strtolower($parts['scheme']);
        if ($scheme !== 'http' && $scheme !== 'https') {
            return null;
        }

        $basePath = '';
        if (!empty($parts['path']) && $parts['path'] !== '/') {
            $basePath = rtrim($parts['path'], '/');
        }

        self::$install = array(
            'basePath' => $basePath
        );
        return self::$install;
    }

    private static function splitHref($href)
    {
        $fragment = null;
        $query = null;
        $hash = strpos($href, '#');
        if ($hash !== false) {
            $fragment = substr($href, $hash + 1);
            $href = substr($href, 0, $hash);
        }
        $question = strpos($href, '?');
        if ($question !== false) {
            $query = substr($href, $question + 1);
            $href = substr($href, 0, $question);
        }
        return array($href, $query, $fragment);
    }

    /**
     * The editor document lives at {base}/admin/articles/{id}. TinyMCE drops
     * the last segment and resolves pasted links from {base}/admin/articles/.
     */
    private static function resolveAgainstEditor($relative, $basePath)
    {
        $directory = ($basePath === '' ? '' : $basePath) . '/admin/articles';
        $stack = explode('/', $directory);
        foreach (explode('/', $relative) as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }
            if ($part === '..') {
                if (count($stack) > 1) {
                    array_pop($stack);
                }
                continue;
            }
            $stack[] = $part;
        }

        $path = implode('/', $stack);
        if ($path === '') {
            // Popped back to the host root. implode of array('') is empty.
            return '/';
        }
        if ($path[0] !== '/') {
            return null;
        }
        return $path;
    }

    private static function canonicalPublicPath($path, $basePath)
    {
        if ($path === '' || $path[0] !== '/') {
            return null;
        }

        $trimmed = rtrim($path, '/');
        if ($trimmed === '') {
            $trimmed = '/';
        }

        $prefix = $basePath === '' ? '' : $basePath;
        if ($prefix === '') {
            if ($trimmed === '/') {
                return '/';
            }
        } else {
            if ($trimmed === $prefix) {
                return $prefix . '/';
            }
            if (strpos($trimmed, $prefix . '/') !== 0) {
                return null;
            }
        }

        $rest = $prefix === '' ? $trimmed : substr($trimmed, strlen($prefix));
        if (preg_match('#\A/(?:article|category)/[^/]+\z#', $rest) === 1) {
            return $prefix . $rest;
        }
        if (preg_match('#\A/attachment/[0-9]+\z#', $rest) === 1) {
            return $prefix . $rest;
        }
        if ($rest === '/search') {
            return ($prefix === '' ? '' : $prefix) . '/search';
        }
        return null;
    }

    private static function formatUrl($path, $query, $fragment)
    {
        $url = $path;
        if ($query !== null) {
            $url .= '?' . $query;
        }
        if ($fragment !== null) {
            $url .= '#' . $fragment;
        }
        return $url;
    }
}
