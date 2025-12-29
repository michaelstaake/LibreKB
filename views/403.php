<?php
// Detect base path from config if available
$basePath = '';
if (class_exists('Config') && method_exists('Config', 'get')) {
    $systemURL = Config::get('systemURL');
    if ($systemURL) {
        $basePath = rtrim(parse_url($systemURL, PHP_URL_PATH), '/');
    }
} else {
    // Fallback: try to detect from request URI
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    
    // Get the directory path of the script
    $scriptDir = dirname($scriptName);
    if ($scriptDir !== '/' && $scriptDir !== '.') {
        $basePath = $scriptDir;
    }
}
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Access Forbidden - <?php echo htmlspecialchars($siteName ?? 'Knowledge Base'); ?></title>
        <link href="<?php echo $basePath; ?>/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
        <link href="<?php echo $basePath; ?>/css/other.css" rel="stylesheet" type="text/css">
    </head>
    <body class="error-403">
        <div class="container">
            <div class="error-container">
                <div class="error-header">
                    <div class="error-icon">
                        <i class="bi bi-lock"></i>
                    </div>
                    <div class="error-code">403</div>
                    <h1 class="error-title">Access Forbidden</h1>
                    <p class="error-subtitle">You don't have permission to access this resource.</p>
                    <p class="error-description">
                        This page or action requires higher privileges than your current account has. 
                        Please contact an administrator if you believe you should have access to this content.
                    </p>
                </div>
                
                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                    <a href="<?php echo $basePath; ?>/" class="btn btn-primary">
                        <i class="bi bi-house-door me-2"></i>Go Home
                    </a>
                    <a href="javascript:history.back()" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Go Back
                    </a>
                </div>
            </div>
        </div>
        
        <script src="<?php echo $basePath; ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
