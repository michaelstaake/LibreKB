<?php
/**
 * LibreKB - Knowledge Base Application
 * Main entry point for the application
 */

// Start session
session_start();

// Define constants
define('ROOT_PATH', __DIR__);
define('CORE_PATH', ROOT_PATH . '/core');
define('CONTROLLERS_PATH', ROOT_PATH . '/controllers');
define('MODELS_PATH', ROOT_PATH . '/models');
define('VIEWS_PATH', ROOT_PATH . '/views');

// Autoloader
spl_autoload_register(function ($class) {
    $paths = [
        CORE_PATH . '/' . $class . '.php',
        CONTROLLERS_PATH . '/' . $class . '.php',
        MODELS_PATH . '/' . $class . '.php',
        ROOT_PATH . '/classes/' . $class . '.php'
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Load configuration
require_once 'config.php';

// Check if we're accessing the install page or test pages - if so, skip database checks
$requestUri = $_SERVER['REQUEST_URI'];

// Detect base path for subfolder installations
$basePath = '';
if (class_exists('Config') && method_exists('Config', 'get')) {
    $systemURL = Config::get('systemURL');
    if ($systemURL) {
        $basePath = rtrim(parse_url($systemURL, PHP_URL_PATH), '/');
    }
} else {
    // Fallback: detect from script name
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    if ($scriptDir !== '/' && $scriptDir !== '.') {
        $basePath = $scriptDir;
    }
}

// Strip base path from request URI for proper route matching
$routePath = $requestUri;
if ($basePath && strpos($requestUri, $basePath) === 0) {
    $routePath = substr($requestUri, strlen($basePath));
}

$isInstallPage = (strpos($routePath, '/install') === 0);
$isTestPage = (strpos($routePath, '/test-') === 0);

// Check database tables exist (unless we're on install page or test pages)
if (!$isInstallPage && !$isTestPage) {
    try {
        $database = new Database();
        $missingTables = $database->checkRequiredTables();
        
        if (!empty($missingTables)) {
            // Show database error page
            include VIEWS_PATH . '/database-error.php';
            exit;
        }
    } catch (Exception $e) {
        // If we can't even connect to database, show error page
        $missingTables = ['users', 'settings', 'articles', 'categories'];
        include VIEWS_PATH . '/database-error.php';
        exit;
    }
}

// Initialize the router
$router = new Router();

// Load routes
require_once 'routes.php';

// Dispatch the request
$router->dispatch();
