<?php

class Router
{
    private $routes = [];
    private $middleware = [];
    private $currentGroup = '';
    private static $basePath = null;
    
    private function getBasePath()
    {
        if (self::$basePath === null) {
            try {
                $config = new Config();
                $systemURL = rtrim($config->systemURL, '/');
                
                // Parse the URL to get the path component
                $parsedUrl = parse_url($systemURL);
                self::$basePath = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';
            } catch (Exception $e) {
                // If config fails, assume root path
                self::$basePath = '';
            }
        }
        
        return self::$basePath;
    }
    
    public function get($path, $handler)
    {
        $this->addRoute('GET', $path, $handler);
    }
    
    public function post($path, $handler)
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function options($path, $handler)
    {
        $this->addRoute('OPTIONS', $path, $handler);
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
        $uri = rawurldecode(strtok($_SERVER['REQUEST_URI'], '?'));
        
        // Get base path from config
        $basePath = $this->getBasePath();
        
        // Remove base path from URI if present
        if (!empty($basePath) && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }
        
        // Remove trailing slash and clean URI
        $uri = rtrim($uri, '/');
        if (empty($uri)) {
            $uri = '/';
        }
        
        // Handle legacy URLs and query parameters
        $uri = $this->handleLegacyUrls($uri);
        
        $matchedPath = false;

        foreach ($this->routes as $route) {
            if (!$this->matchRoute($route['path'], $uri, $params)) {
                continue;
            }

            if ($route['method'] !== $method) {
                $matchedPath = true;
                continue;
            }

            if ($method === 'POST' && !$this->csrfExempt($uri) && !$this->oversizedArticleBody($route['handler'])) {
                if (!Csrf::validate()) {
                    $this->rejectCsrf();
                    return;
                }
            }

            // Check middleware
            foreach ($route['middleware'] as $middleware) {
                if (!$this->runMiddleware($middleware)) {
                    return;
                }
            }

            // Execute the handler
            return $this->executeHandler($route['handler'], $params);
        }

        if ($this->isApiUri($uri)) {
            $status = $matchedPath ? 405 : 404;
            $message = $matchedPath ? 'Method not allowed.' : 'Not found.';
            $this->rejectApi($status, $message);
            return;
        }

        // No route found - 404
        $this->handle404();
    }

    private function isApiUri($uri)
    {
        return $uri === '/api' || strpos($uri, '/api/') === 0;
    }

    /**
     * API paths always answer with JSON, including unknown routes and
     * methods other than GET and OPTIONS.
     */
    private function rejectApi($status, $message)
    {
        if (class_exists('ApiController')) {
            $controller = new ApiController();
            $controller->reject($status, $message);
        }

        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        if ($status === 405) {
            header('Allow: GET, OPTIONS');
        }
        echo json_encode(['error' => $message]);
        exit;
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

    /**
     * Search only reads. Legacy /search?query= links are rewritten into POST
     * and have no token, so they stay exempt.
     */
    private function csrfExempt($uri)
    {
        return $uri === '/search';
    }

    /**
     * PHP empties $_POST when the body exceeds post_max_size, so the token
     * cannot be present. Article save already turns that into an error and
     * does not write. Other POSTs still require a token.
     */
    private function oversizedArticleBody($handler)
    {
        $length = isset($_SERVER['CONTENT_LENGTH']) ? (int)$_SERVER['CONTENT_LENGTH'] : 0;
        if ($length <= 0 || !empty($_POST) || !empty($_FILES)) {
            return false;
        }
        return $handler === 'ArticleController@store' || $handler === 'ArticleController@update';
    }

    private function rejectCsrf()
    {
        http_response_code(403);
        header('Content-Type: text/html; charset=UTF-8');
        echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><title>Request rejected</title></head><body>';
        echo '<h1>Request rejected</h1>';
        echo '<p>The form expired or the security token was missing. Go back, reload the page, and try again.</p>';
        echo '</body></html>';
        exit;
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
            $basePath = $this->getBasePath();
            $loginUrl = $basePath . '/login';
            header('Location: ' . $loginUrl);
            exit;
        }
        return true;
    }
    
    private function checkRole($allowedRoles)
    {
        // First check if user is authenticated
        if (!isset($_SESSION['user_id'])) {
            $basePath = $this->getBasePath();
            $loginUrl = $basePath . '/login';
            header('Location: ' . $loginUrl);
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
                    // Pass values in route order. An associative array would be
                    // treated as named arguments, which breaks handlers that do
                    // not declare those names (for example an OPTIONS preflight).
                    return call_user_func_array([$controller, $method], array_values($params));
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
