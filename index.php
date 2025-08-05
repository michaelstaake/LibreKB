<?php
/**
 * LibreKB - Knowledge Base Application
 * Main entry point for the application
 */

// Start session
session_start();

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
$isInstallPage = (strpos($requestUri, '/install') === 0);
$isTestPage = (strpos($requestUri, '/test-') === 0);

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
