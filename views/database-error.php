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
        <title>Database Setup Required - LibreKB</title>
        <link href="<?php echo $basePath; ?>/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
        <link href="<?php echo $basePath; ?>/css/other.css" rel="stylesheet" type="text/css">
    </head>
    <body class="error-database">
        <div class="container">
            <div class="error-container">
                <div class="error-header">
                    <div class="error-icon">
                        <i class="bi bi-database-x"></i>
                    </div>
                    <h1 class="error-title">Database Setup Required</h1>
                    <p class="error-subtitle">LibreKB needs to be installed before you can continue.</p>
                </div>
                
                <div class="error-details">
                    <h5>
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Missing Database Tables
                    </h5>
                    <p class="mb-2 text-muted">The following required database tables were not found:</p>
                    <ul class="missing-tables">
                        <?php 
                        // Ensure $missingTables is defined and is an array
                        if (!isset($missingTables) || !is_array($missingTables)) {
                            $missingTables = ['users', 'settings', 'articles', 'categories', 'log'];
                        }
                        foreach ($missingTables as $table): 
                        ?>
                            <li><?php echo htmlspecialchars($table); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="solution-box">
                    <h5>
                        <i class="bi bi-lightbulb me-2"></i>
                        Quick Solution
                    </h5>
                    <p class="solution-text">
                        Run the LibreKB installer to automatically create all required database tables and set up your knowledge base.
                    </p>
                    <a href="<?php echo $basePath; ?>/install" class="btn btn-success">
                        <i class="bi bi-play-circle me-2"></i>
                        Run Installer
                    </a>
                </div>
            </div>
        </div>
        
        <script src="<?php echo $basePath; ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
