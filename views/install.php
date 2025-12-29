<?php
require_once('config.php');
$current = new Version();
$pageTitle = 'LibreKB Installer';

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
        <title><?php echo($pageTitle); ?></title>
        <link href="<?php echo $basePath; ?>/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
        <link href="<?php echo $basePath; ?>/css/other.css" rel="stylesheet" type="text/css">
    </head>
    <body class="install-page">
        <div class="container">
            <div class="install-container">
                <div class="install-header">
                    <div class="install-icon">
                        <i class="bi bi-download"></i>
                    </div>
                    <h1 class="install-title">LibreKB Installer</h1>
                    <div class="version-badge">
                        Version <?php echo $current->version; ?>
                    </div>
                </div>
                
                <?php
                    if (isset($error) && !empty($error)) {
                        echo '<div class="alert alert-danger" role="alert"><i class="bi bi-exclamation-triangle me-2"></i>' . htmlspecialchars($error) . '</div>';
                    }
                    if (isset($message) && !empty($message)) {
                        echo '<div class="alert alert-success" role="alert"><i class="bi bi-check-circle me-2"></i>' . htmlspecialchars($message) . '</div>';
                        if ($message === 'Installation completed successfully.') {
                            echo '<div class="info-text"><strong>Next step:</strong> go to <a href="' . $basePath . '/admin/" class="external-link">admin panel</a> to continue.</div>';
                            exit;
                        }
                    }
                ?>
                
                <div class="info-text">
                    Before you begin, please make sure you have configured your database information in <code>config.php</code>.
                    For more information, visit <a href="https://librekb.com/" target="_blank" class="external-link">librekb.com</a>.
                </div>
                
                <h3 class="section-title">
                    <i class="bi bi-server me-2"></i>Server Compatibility Check
                </h3>
                
                <div class="compatibility-check mb-4">
                    <?php
                    $phpVersion = phpversion();
                    $requiredVersion = '8.4.0';
                    $isPhpCompatible = version_compare($phpVersion, $requiredVersion, '>=');
                    
                    // Check web server
                    $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
                    $webServer = 'Unknown';
                    $isWebServerCompatible = false;
                    
                    if (stripos($serverSoftware, 'apache') !== false) {
                        $webServer = 'Apache';
                        $isWebServerCompatible = true;
                    } elseif (stripos($serverSoftware, 'lighttpd') !== false) {
                        $webServer = 'Lighttpd';
                        $isWebServerCompatible = true;
                    } elseif (stripos($serverSoftware, 'development') !== false || php_sapi_name() === 'cli-server') {
                        $webServer = 'PHP Built-in Server';
                        $isWebServerCompatible = true;
                    } elseif (stripos($serverSoftware, 'litespeed') !== false) {
                        $webServer = 'LiteSpeed';
                        $isWebServerCompatible = true;
                    } elseif (stripos($serverSoftware, 'openlitespeed') !== false) {
                        $webServer = 'OpenLiteSpeed';
                        $isWebServerCompatible = true;
                    } else {
                        $webServer = $serverSoftware;
                        // Other servers don't support .htaccess natively
                        $isWebServerCompatible = false;
                    }
                    
                    // Check PHP cURL extension
                    $isCurlAvailable = extension_loaded('curl');
                    
                    // Check PHP mbstring extension
                    $isMbstringAvailable = extension_loaded('mbstring');
                    
                    // Check all PHP extensions
                    $requiredExtensions = ['curl', 'mbstring'];
                    $missingExtensions = [];
                    $extensionStatus = [];
                    
                    foreach ($requiredExtensions as $ext) {
                        $isLoaded = extension_loaded($ext);
                        $extensionStatus[$ext] = $isLoaded;
                        if (!$isLoaded) {
                            $missingExtensions[] = $ext;
                        }
                    }
                    
                    $areExtensionsCompatible = empty($missingExtensions);
                    
                    // Get database connection status from controller
                    $dbConnection = isset($dbConnection) ? $dbConnection : ['success' => false, 'message' => 'Database connection not checked', 'error' => 'Unknown error'];
                    
                    // Check if requirements bypass is enabled
                    $bypassRequirements = false;
                    if (class_exists('Config') && method_exists('Config', 'get')) {
                        $bypassRequirements = Config::get('bypassRequirements');
                    } elseif (class_exists('Config')) {
                        $config = new Config();
                        $bypassRequirements = isset($config->bypassRequirements) ? $config->bypassRequirements : false;
                    }

                    $isFullyCompatible = $isPhpCompatible && $isWebServerCompatible && $areExtensionsCompatible && $dbConnection['success'];
                    
                    if ($bypassRequirements) {
                        $isFullyCompatible = true;
                    }
                    ?>
                    
                    <?php if ($bypassRequirements): ?>
                    <div class="alert alert-warning mb-4">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Requirements Bypass Enabled:</strong> System checks are being ignored.
                    </div>
                    <?php endif; ?>

                    <div class="compatibility-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="compatibility-label">
                                <i class="bi bi-database me-2"></i>Database Connection
                            </span>
                            <div class="d-flex align-items-center">
                                <span class="compatibility-value me-2">
                                    <?php echo $dbConnection['success'] ? 'OK' : 'Failed'; ?>
                                </span>
                                <?php if ($dbConnection['success']): ?>
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                <?php else: ?>
                                    <i class="bi bi-x-circle-fill text-danger"></i>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="compatibility-requirement">
                            <?php if ($dbConnection['success']): ?>
                                Established MySQL Database Connection
                            <?php else: ?>
                                <span class="text-danger">
                                    <?php echo htmlspecialchars($dbConnection['message']); ?>
                                    <?php if (!empty($dbConnection['error'])): ?>
                                        <br><small>Error: <?php echo htmlspecialchars($dbConnection['error']); ?></small>
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="compatibility-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="compatibility-label">
                                <i class="bi bi-code-slash me-2"></i>PHP Version
                            </span>
                            <div class="d-flex align-items-center">
                                <span class="compatibility-value me-2"><?php echo $phpVersion; ?></span>
                                <?php if ($isPhpCompatible): ?>
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                <?php else: ?>
                                    <i class="bi bi-x-circle-fill text-danger"></i>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="compatibility-requirement">
                            Requires PHP <?php echo $requiredVersion; ?> or higher.
                        </div>
                    </div>
                    
                    <div class="compatibility-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="compatibility-label">
                                <i class="bi bi-puzzle me-2"></i>PHP Extensions
                            </span>
                            <div class="d-flex align-items-center">
                                <span class="compatibility-value me-2">
                                    <?php echo $areExtensionsCompatible ? 'OK' : count($missingExtensions) . ' Missing'; ?>
                                </span>
                                <?php if ($areExtensionsCompatible): ?>
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                <?php else: ?>
                                    <i class="bi bi-x-circle-fill text-danger"></i>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="compatibility-requirement">
                            Required: curl, mbstring
                        </div>
                    </div>

                    <div class="compatibility-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="compatibility-label">
                                <i class="bi bi-globe me-2"></i>Web Server
                            </span>
                            <div class="d-flex align-items-center">
                                <span class="compatibility-value me-2"><?php echo htmlspecialchars($webServer); ?></span>
                                <?php if ($isWebServerCompatible): ?>
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                <?php else: ?>
                                    <i class="bi bi-x-circle-fill text-danger"></i>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="compatibility-requirement">
                            Requires Apache-style .htaccess support (Apache, LiteSpeed, OpenLiteSpeed, Lighttpd)
                            <?php if (!$isWebServerCompatible): ?>
                                <div class="text-danger mt-1">
                                    <small>
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        Your web server does not support .htaccess files. LibreKB requires .htaccess for URL rewriting.
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <?php if (!$isFullyCompatible): ?>
                <div class="alert alert-danger" role="alert">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>Unable to Continue</strong><br>
                            Please address any issues and refresh this page to proceed with the installation.
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <form action="<?php echo $basePath; ?>/install" method="POST" <?php echo (!$isFullyCompatible) ? 'style="display: none;"' : ''; ?>>
                    <h3 class="section-title">
                        <i class="bi bi-person-plus me-2"></i>Create Admin User
                    </h3>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="bi bi-envelope me-2"></i>Email Address
                        </label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock me-2"></i>Password
                        </label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-play-circle me-2"></i>Run Installer
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <script src="<?php echo $basePath; ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script>
            // Add loading state when installation form is submitted
            document.addEventListener('DOMContentLoaded', function() {
                const installForm = document.querySelector('form[action*="/install"]');
                const submitButton = document.querySelector('button[type="submit"]');
                
                if (installForm && submitButton) {
                    installForm.addEventListener('submit', function() {
                        submitButton.disabled = true;
                        submitButton.innerHTML = '<i class="bi bi-arrow-repeat spinner-border spinner-border-sm me-2" role="status"></i>Installing...';
                    });
                }
            });
        </script>
    </body>
</html>
?>