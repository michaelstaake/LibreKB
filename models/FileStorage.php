<?php

/**
 * Reusable upload storage.
 *
 * Article attachments use the "attachments" purpose. A later feature (for
 * example category icons) can pass another purpose and get the same checks:
 * extension allowlist, size limit, random stored names, and a directory that
 * is not executable and not served directly.
 */
class FileStorage
{
    const MAX_BYTES = 10485760;
    const MAX_FILES = 10;

    private static $purposes = [
        'attachments'
    ];

    private static $dangerousExtensions = [
        'php', 'phtml', 'php3', 'php4', 'php5', 'phar', 'pht',
        'exe', 'sh', 'bat', 'cmd', 'com', 'cgi', 'pl', 'py',
        'js', 'html', 'htm', 'svg', 'asp', 'aspx', 'jsp', 'htaccess'
    ];

    private static $blockedMimes = [
        'application/x-httpd-php',
        'application/x-php',
        'text/x-php',
        'text/html',
        'application/xhtml+xml',
        'image/svg+xml',
        'application/javascript',
        'text/javascript',
        'application/x-httpd-php-source'
    ];

    /**
     * Extension => MIME types finfo is allowed to report for that extension.
     * Office formats are ZIP containers, so application/zip is accepted for those.
     */
    private static $allowed = [
        'pdf' => ['application/pdf'],
        'txt' => ['text/plain'],
        'csv' => ['text/plain', 'text/csv', 'application/csv', 'application/vnd.ms-excel'],
        'rtf' => ['text/rtf', 'application/rtf'],
        'doc' => ['application/msword', 'application/octet-stream'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/octet-stream'],
        'xls' => ['application/vnd.ms-excel', 'application/octet-stream'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip', 'application/octet-stream'],
        'ppt' => ['application/vnd.ms-powerpoint', 'application/octet-stream'],
        'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/zip', 'application/octet-stream'],
        'odt' => ['application/vnd.oasis.opendocument.text', 'application/zip', 'application/octet-stream'],
        'ods' => ['application/vnd.oasis.opendocument.spreadsheet', 'application/zip', 'application/octet-stream'],
        'odp' => ['application/vnd.oasis.opendocument.presentation', 'application/zip', 'application/octet-stream'],
        'png' => ['image/png'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'gif' => ['image/gif'],
        'webp' => ['image/webp'],
        'zip' => ['application/zip', 'application/x-zip-compressed', 'application/octet-stream']
    ];

    public static function allowedExtensions()
    {
        return array_keys(self::$allowed);
    }

    public static function formatSize($bytes)
    {
        $bytes = (int)$bytes;
        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return round($bytes / 1048576, 1) . ' MB';
    }

    /**
     * Turn a PHP $_FILES entry (single or multiple) into a list of file arrays.
     * Empty selections are skipped.
     */
    public static function normalizeUploadedFiles($field)
    {
        if (!isset($_FILES[$field]) || !is_array($_FILES[$field])) {
            return [];
        }

        $files = $_FILES[$field];
        $normalized = [];

        if (is_array($files['name'])) {
            $count = count($files['name']);
            for ($i = 0; $i < $count; $i++) {
                if (!isset($files['error'][$i]) || $files['error'][$i] === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                $normalized[] = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i] ?? '',
                    'tmp_name' => $files['tmp_name'][$i] ?? '',
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i] ?? 0
                ];
            }
            return $normalized;
        }

        if (!isset($files['error']) || $files['error'] === UPLOAD_ERR_NO_FILE) {
            return [];
        }

        return [$files];
    }

    /**
     * Validate and store an uploaded file. Returns metadata for the caller to record.
     */
    public function store(array $file, $purpose = 'attachments')
    {
        $this->assertPurpose($purpose);

        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException($this->uploadErrorMessage($file['error'] ?? UPLOAD_ERR_NO_FILE));
        }

        $tmp = $file['tmp_name'] ?? '';
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new RuntimeException('Upload was rejected.');
        }

        $originalName = $this->sanitizeOriginalName($file['name'] ?? '', $this->extensionFromName($file['name'] ?? ''));
        $extension = $this->extensionFromName($file['name'] ?? '');
        $size = (int)($file['size'] ?? 0);
        $mime = $this->assertAllowedContents($tmp, $extension, $size);

        $this->ensureDirectory($purpose);
        $storedName = $this->uniqueStoredName($purpose, $extension);
        $destination = $this->absolutePath($storedName, $purpose);

        if (!$destination || !move_uploaded_file($tmp, $destination)) {
            throw new RuntimeException('The file could not be stored.');
        }

        chmod($destination, 0644);

        return [
            'original_name' => $originalName,
            'stored_name' => $storedName,
            'extension' => $extension,
            'mime_type' => $mime,
            'size' => filesize($destination) ?: $size
        ];
    }

    /**
     * Check a local file against the allowlist. Used by store() and by tests.
     * Does not accept a path from a request by itself.
     */
    public function assertAllowedContents($path, $extension, $size)
    {
        $extension = strtolower((string)$extension);
        if (!isset(self::$allowed[$extension])) {
            throw new RuntimeException('This file type is not allowed.');
        }

        $size = (int)$size;
        if ($size <= 0 || $size > self::MAX_BYTES) {
            throw new RuntimeException('Each file must be 10 MB or smaller.');
        }

        if (!is_file($path)) {
            throw new RuntimeException('Upload was rejected.');
        }

        $actualSize = filesize($path);
        if ($actualSize === false || $actualSize <= 0 || $actualSize > self::MAX_BYTES) {
            throw new RuntimeException('Each file must be 10 MB or smaller.');
        }

        $mime = $this->detectMime($path);
        if (in_array($mime, self::$blockedMimes, true) || !in_array($mime, self::$allowed[$extension], true)) {
            throw new RuntimeException('The file contents do not match an allowed type.');
        }

        return $mime;
    }

    public function delete($storedName, $purpose = 'attachments')
    {
        $path = $this->absolutePath($storedName, $purpose);
        if ($path && is_file($path)) {
            unlink($path);
        }
    }

    public function absolutePath($storedName, $purpose = 'attachments')
    {
        if (!$this->isSafeStoredName($storedName) || !in_array($purpose, self::$purposes, true)) {
            return null;
        }

        return $this->directory($purpose) . '/' . $storedName;
    }

    public function isSafeStoredName($storedName)
    {
        if (!is_string($storedName) || preg_match('/^[a-f0-9]{32}\.([a-z0-9]{1,10})$/', $storedName, $matches) !== 1) {
            return false;
        }

        return isset(self::$allowed[$matches[1]]);
    }

    public function mimeForExtension($extension)
    {
        $extension = strtolower((string)$extension);
        if (!isset(self::$allowed[$extension])) {
            return 'application/octet-stream';
        }
        return self::$allowed[$extension][0];
    }

    public function extensionFromName($name)
    {
        $name = str_replace(["\0", "\\"], ['', '/'], (string)$name);
        $base = basename($name);
        $parts = explode('.', $base);
        if (count($parts) < 2) {
            throw new RuntimeException('The file needs an allowed extension.');
        }

        $extension = strtolower((string)array_pop($parts));
        if (!isset(self::$allowed[$extension]) || !preg_match('/^[a-z0-9]{1,10}$/', $extension)) {
            throw new RuntimeException('File type .' . $extension . ' is not allowed.');
        }

        foreach ($parts as $part) {
            if (in_array(strtolower($part), self::$dangerousExtensions, true)) {
                throw new RuntimeException('This file name is not allowed.');
            }
        }

        return $extension;
    }

    private function sanitizeOriginalName($name, $extension)
    {
        $name = str_replace(["\0", "\r", "\n", "\\"], '', (string)$name);
        $name = basename(str_replace('\\', '/', $name));
        $name = preg_replace('/[^A-Za-z0-9._ -]/', '_', $name);
        $name = trim($name, '. ');

        if ($name === '') {
            $name = 'download.' . $extension;
        }

        if (strlen($name) > 180) {
            $name = substr($name, 0, 180);
        }

        if (!preg_match('/\.' . preg_quote($extension, '/') . '$/i', $name)) {
            $name .= '.' . $extension;
        }

        return $name;
    }

    private function detectMime($path)
    {
        if (!class_exists('finfo')) {
            throw new RuntimeException('File type detection is not available on this server.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($path);
        if (!$mime) {
            throw new RuntimeException('The file type could not be determined.');
        }

        return strtolower($mime);
    }

    private function uniqueStoredName($purpose, $extension)
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $name = bin2hex(random_bytes(16)) . '.' . $extension;
            $path = $this->absolutePath($name, $purpose);
            if ($path && !file_exists($path)) {
                return $name;
            }
        }

        throw new RuntimeException('The file could not be stored.');
    }

    private function assertPurpose($purpose)
    {
        if (!in_array($purpose, self::$purposes, true)) {
            throw new RuntimeException('Unknown upload destination.');
        }
    }

    private function directory($purpose)
    {
        $root = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__);
        return $root . '/storage/uploads/' . $purpose;
    }

    private function ensureDirectory($purpose)
    {
        $dir = $this->directory($purpose);
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('The upload directory is not writable.');
        }

        if (!is_writable($dir)) {
            throw new RuntimeException('The upload directory is not writable.');
        }

        $root = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__);
        $this->ensureDenyHtaccess($root . '/storage');
        $this->ensureDenyHtaccess($root . '/storage/uploads');
        $this->ensureDenyHtaccess($dir);
    }

    private function ensureDenyHtaccess($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $file = $dir . '/.htaccess';
        if (is_file($file)) {
            return;
        }

        $rules = "<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n"
            . "<IfModule !mod_authz_core.c>\n    Deny from all\n</IfModule>\n"
            . "Options -Indexes\n";
        file_put_contents($file, $rules);
    }

    private function uploadErrorMessage($code)
    {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'The file exceeds the server upload limit.';
            case UPLOAD_ERR_PARTIAL:
                return 'The file was only partially uploaded.';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'The server is missing a temporary folder for uploads.';
            case UPLOAD_ERR_CANT_WRITE:
                return 'The server could not write the uploaded file.';
            case UPLOAD_ERR_EXTENSION:
                return 'A server extension stopped the upload.';
            default:
                return 'The file could not be uploaded.';
        }
    }
}
