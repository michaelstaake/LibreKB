<?php

/**
 * Builds the article editor toolbar from a fixed list of buttons.
 * An empty stored value is the toolbar LibreKB ships with.
 */
class EditorToolbar
{
    const DEFAULT_TOOLBAR = 'undo redo | bold italic underline | blocks | fontsize | bullist numlist | link table | code | searchreplace | wordcount';

    public static function catalog()
    {
        return [
            'undo' => self::button('Undo', 'History', 'history', '', true, 'icon', 'arrow-counterclockwise'),
            'redo' => self::button('Redo', 'History', 'history', '', true, 'icon', 'arrow-clockwise'),
            'bold' => self::button('Bold', 'Format', 'format', '', true, 'icon', 'type-bold'),
            'italic' => self::button('Italic', 'Format', 'format', '', true, 'icon', 'type-italic'),
            'underline' => self::button('Underline', 'Format', 'format', '', true, 'icon', 'type-underline'),
            'forecolor' => self::button('Font color', 'Format', 'format', '', false, 'color', ''),
            'backcolor' => self::button('Highlight', 'Format', 'format', '', false, 'icon', 'highlighter'),
            'strikethrough' => self::button('Strikethrough', 'Format', 'format', '', false, 'icon', 'type-strikethrough'),
            'subscript' => self::button('Subscript', 'Format', 'format', '', false, 'icon', 'subscript'),
            'superscript' => self::button('Superscript', 'Format', 'format', '', false, 'icon', 'superscript'),
            'alignleft' => self::button('Align left', 'Alignment', 'align', '', false, 'icon', 'text-left'),
            'aligncenter' => self::button('Center', 'Alignment', 'align', '', false, 'icon', 'text-center'),
            'alignright' => self::button('Align right', 'Alignment', 'align', '', false, 'icon', 'text-right'),
            'alignjustify' => self::button('Justify', 'Alignment', 'align', '', false, 'icon', 'justify'),
            'blocks' => self::button('Paragraph style', 'Structure', 'blocks', '', true, 'wide', 'Paragraph'),
            'fontsize' => self::button('Font size', 'Structure', 'fontsize', '', true, 'wide', '12pt'),
            'bullist' => self::button('Bulleted list', 'Structure', 'lists', 'lists', true, 'icon', 'list-ul'),
            'numlist' => self::button('Numbered list', 'Structure', 'lists', 'lists', true, 'icon', 'list-ol'),
            'link' => self::button('Link', 'Insert', 'insert', 'link', true, 'icon', 'link-45deg'),
            'image' => self::button('Image', 'Insert', 'insert', 'image', false, 'icon', 'image'),
            'table' => self::button('Table', 'Insert', 'insert', 'table', true, 'icon', 'table'),
            'blockquote' => self::button('Block quote', 'Insert', 'insert', '', false, 'icon', 'quote'),
            'hr' => self::button('Horizontal line', 'Insert', 'insert', '', false, 'icon', 'hr'),
            'code' => self::button('Source code', 'Tools', 'code', 'code', true, 'icon', 'code-slash'),
            'searchreplace' => self::button('Find and replace', 'Tools', 'search', 'searchreplace', true, 'icon', 'search'),
            'wordcount' => self::button('Word count', 'Tools', 'words', 'wordcount', true, 'icon', 'hash')
        ];
    }

    public static function defaultButtons()
    {
        $buttons = [];
        foreach (self::catalog() as $id => $button) {
            if ($button['default']) {
                $buttons[] = $id;
            }
        }
        return $buttons;
    }

    public static function sanitize($posted)
    {
        if (!is_array($posted)) {
            return [];
        }

        $catalog = self::catalog();
        $buttons = [];
        foreach ($posted as $id) {
            $id = (string)$id;
            if (isset($catalog[$id]) && !in_array($id, $buttons, true)) {
                $buttons[] = $id;
            }
        }
        return $buttons;
    }

    public static function buttonsFromStored($stored)
    {
        $stored = trim((string)$stored);
        if ($stored === '') {
            return self::defaultButtons();
        }

        $tokens = preg_split('/\s+/', str_replace('|', ' ', $stored));
        $buttons = self::sanitize($tokens);
        if (empty($buttons)) {
            return self::defaultButtons();
        }
        return $buttons;
    }

    public static function toString($buttons)
    {
        $catalog = self::catalog();
        $groups = [];
        $current = null;
        foreach (self::sanitize($buttons) as $id) {
            $group = $catalog[$id]['group'];
            if ($current !== $group) {
                $groups[] = [];
                $current = $group;
            }
            $groups[count($groups) - 1][] = $id;
        }

        $parts = [];
        foreach ($groups as $group) {
            $parts[] = implode(' ', $group);
        }
        return implode(' | ', $parts);
    }

    public static function storedValue($buttons)
    {
        $string = self::toString($buttons);
        if ($string === '' || $string === self::DEFAULT_TOOLBAR) {
            return '';
        }
        return $string;
    }

    public static function pluginsFor($buttons)
    {
        $catalog = self::catalog();
        $plugins = [];
        foreach (self::sanitize($buttons) as $id) {
            $plugin = $catalog[$id]['plugin'];
            if ($plugin !== '' && !in_array($plugin, $plugins, true)) {
                $plugins[] = $plugin;
            }
        }
        return $plugins;
    }

    public static function addedCount($buttons)
    {
        $defaults = array_flip(self::defaultButtons());
        $added = 0;
        foreach (self::sanitize($buttons) as $id) {
            if (!isset($defaults[$id])) {
                $added++;
            }
        }
        return $added;
    }

    public static function navigationStatus($stored)
    {
        $buttons = self::buttonsFromStored($stored);
        if (trim((string)$stored) === '' || self::toString($buttons) === self::DEFAULT_TOOLBAR) {
            return 'Default toolbar';
        }

        $added = self::addedCount($buttons);
        if ($added === 1) {
            return '1 button added';
        }
        if ($added > 1) {
            return $added . ' buttons added';
        }
        return 'Custom toolbar';
    }

    /**
     * Enabled buttons stay in the saved order. Buttons that are off slot back
     * in beside the catalog button that comes before them.
     */
    public static function displayOrder($stored)
    {
        $display = self::buttonsFromStored($stored);
        $catalog = array_keys(self::catalog());

        foreach ($catalog as $index => $id) {
            if (in_array($id, $display, true)) {
                continue;
            }

            $insertAt = 0;
            for ($previous = $index - 1; $previous >= 0; $previous--) {
                $position = array_search($catalog[$previous], $display, true);
                if ($position !== false) {
                    $insertAt = $position + 1;
                    break;
                }
            }
            array_splice($display, $insertAt, 0, [$id]);
        }

        return $display;
    }

    private static function button($label, $section, $group, $plugin, $default, $preview, $icon)
    {
        return [
            'label' => $label,
            'section' => $section,
            'group' => $group,
            'plugin' => $plugin,
            'default' => $default,
            'preview' => $preview,
            'icon' => $icon
        ];
    }
}
