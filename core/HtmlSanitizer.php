<?php

/**
 * Allowlist cleaner for article HTML.
 *
 * TinyMCE formatting is kept. Scripts, event handlers, and dangerous URLs
 * are removed on save and again whenever an article is rendered.
 */
class HtmlSanitizer
{
    private static $purifier = null;

    public static function clean($html)
    {
        $html = (string)$html;
        if ($html === '') {
            return '';
        }

        try {
            return self::purifier()->purify($html);
        } catch (Throwable $e) {
            // A cleaner failure must not fall back to raw HTML.
            return htmlspecialchars($html, ENT_QUOTES, 'UTF-8');
        }
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
}
