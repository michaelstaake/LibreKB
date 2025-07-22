<?php

class Router
{
    private $routes = [];
    private $middleware = [];
    private $currentGroup = '';
    
    public function get($path, $handler)
    {
        $this->addRoute('GET', $path, $handler);
    }
    
    public function post($path, $handler)
    {
        $this->addRoute('POST', $path, $handler);
    }
    
    public function group($prefix, $callback)
    {
        $oldGroup = $this->currentGroup;
        $this->currentGroup = rtrim($prefix, '/');
        $callback($this);
        $this->currentGroup = $oldGroup;
    }
    
    public function middleware($middleware, $callback)
    {
        $oldMiddleware = $this->middleware;
        $this->middleware = array_merge($this->middleware, $middleware);
        $callback($this);
        $this->middleware = $oldMiddleware;
    }
    
    private function addRoute($method, $path, $handler)
    {
        $fullPath = $this->currentGroup . $path;
        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'handler' => $handler,
            'middleware' => $this->middleware
        ];
    }
    
    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove trailing slash and clean URI
        $uri = rtrim($uri, '/');
        if (empty($uri)) {
            $uri = '/';
        }
        
        // Handle legacy URLs and query parameters
        $uri = $this->handleLegacyUrls($uri);
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchRoute($route['path'], $uri, $params)) {
                // Check middleware
                foreach ($route['middleware'] as $middleware) {
                    if (!$this->runMiddleware($middleware)) {
                        return;
                    }
                }
                
                // Execute the handler
                return $this->executeHandler($route['handler'], $params);
            }
        }
        
        // No route found - 404
        $this->handle404();
    }
    
    private function handleLegacyUrls($uri)
    {
        // Handle query string routing for backward compatibility
        if (isset($_GET['page'])) {
            switch ($_GET['page']) {
                case 'category':
                    if (isset($_GET['c'])) {
                        return '/category/' . $_GET['c'];
                    }
                    break;
                case 'article':
                    if (isset($_GET['a'])) {
                        return '/article/' . $_GET['a'];
                    }
                    break;
            }
        }
        
        // Handle search
        if ($uri === '/search.php' || $uri === '/search') {
            if (isset($_GET['query']) && !empty($_GET['query'])) {
                $_POST['query'] = $_GET['query'];
                $_SERVER['REQUEST_METHOD'] = 'POST';
            }
            return '/search';
        }
        
        return $uri;
    }
    
    private function matchRoute($routePath, $uri, &$params = [])
    {
        $params = [];
        
        // Convert route path to regex pattern
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';
        
        if (preg_match($pattern, $uri, $matches)) {
            // Extract parameter names
            preg_match_all('/\{([^}]+)\}/', $routePath, $paramNames);
            
            // Map parameter values to names
            for ($i = 1; $i < count($matches); $i++) {
                $paramName = $paramNames[1][$i - 1];
                $params[$paramName] = $matches[$i];
            }
            
            return true;
        }
        
        return false;
    }
    
    private function runMiddleware($middleware)
    {
        switch (true) {
            case $middleware === 'auth':
                return $this->checkAuthentication();
            case strpos($middleware, 'role:') === 0:
                return $this->checkRole(substr($middleware, 5));
            default:
                return true;
        }
    }
    
    private function checkAuthentication()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        return true;
    }
    
    private function checkRole($allowedRoles)
    {
        // First check if user is authenticated
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        // Get user and check role
        $userModel = new User();
        $user = $userModel->getUser($_SESSION['user_id']);
        
        if (!$user) {
            header('Location: /login');
            exit;
        }
        
        // Parse allowed roles
        $roles = array_map('trim', explode(',', $allowedRoles));
        
        if (!in_array($user['group'], $roles)) {
            // Show 403 page instead of redirecting with error message
            $controller = new Controller();
            $controller->show403();
        }
        
        return true;
    }
    
    private function executeHandler($handler, $params)
    {
        if (is_string($handler) && strpos($handler, '@') !== false) {
            list($controllerName, $method) = explode('@', $handler);
            
            if (class_exists($controllerName)) {
                $controller = new $controllerName();
                if (method_exists($controller, $method)) {
                    return call_user_func_array([$controller, $method], $params);
                }
            }
        }
        
        // Handler not found
        $this->handle404();
    }
    
    private function handle404()
    {
        http_response_code(404);
        
        // Get site name for the 404 page
        $siteName = null;
        $configFile = defined('ROOT_PATH') ? ROOT_PATH . '/config.php' : __DIR__ . '/../config.php';
        if (file_exists($configFile)) {
            include_once $configFile;
            if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER')) {
                try {
                    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
                    $stmt = $pdo->prepare("SELECT value FROM settings WHERE name = 'site_name'");
                    $stmt->execute();
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($result) {
                        $siteName = $result['value'];
                    }
                } catch (PDOException $e) {
                    // Silently handle database errors
                }
            }
        }
        
        // Include the 404 view
        $viewFile = defined('ROOT_PATH') ? ROOT_PATH . '/views/404.php' : __DIR__ . '/../views/404.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            // Fallback if 404.php doesn't exist
            echo "404 - Page Not Found";
        }
        exit;
    }
}
