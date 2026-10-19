<?php

class AdminController extends Controller
{
    private $userModel;
    private $settingModel;
    private $categoryModel;
    private $articleModel;
    
    public function __construct()
    {
        $this->userModel = new User();
        $this->settingModel = new Setting();
        $this->categoryModel = new Category();
        $this->articleModel = new Article();
    }
    
    public function login()
    {
        // If already logged in, redirect based on user role
        if (isset($_SESSION['user_id'])) {
            $user = $this->getUser();
            if ($user && ($user['group'] === 'admin' || $user['group'] === 'manager')) {
                return $this->redirect('/admin');
            } else {
                return $this->redirect('/');
            }
        }
        
        $siteName = $this->getSetting('site_name', 'Knowledge Base');
        
        $data = [
            'pageTitle' => 'Login - ' . $siteName,
            'siteName' => $siteName,
            'message' => $this->getMessage(),
            'error' => $this->getError()
        ];
        
        return $this->view('login', $data);
    }
    
    public function authenticate()
    {
        $email = $this->input('email');
        $password = $this->input('password');
        
        $user = $this->userModel->authenticate($email, $password);
        
        if ($user) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            
            // Log successful login
            Log::logAction($user['id'], $user['email'], 'logged in', 'system');
            
            // Redirect based on user group
            if ($user['group'] === 'admin' || $user['group'] === 'manager') {
                return $this->redirect('/admin');
            } else {
                // Client users and other groups go to the frontend
                return $this->redirect('/');
            }
        } else {
            return $this->redirectWithError('/login', 'Login failed. Please check your email and password.');
        }
    }
    
    public function logout()
    {
        $user = $this->getUser();
        
        // Log logout action before destroying session
        if ($user) {
            Log::logAction($user['id'], $user['email'], 'logged out', 'system');
        }
        
        $_SESSION = [];
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'] ?: '/',
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_destroy();
        return $this->redirect('/login');
    }
    
    public function dashboard()
    {
        $user = $this->getUser();
        
        // Get all categories and articles for the dashboard
        $categories = $this->categoryModel->getAllWithArticles();
        $articles = $this->articleModel->getAllWithCategoryName();
        
        // Update article counts to include subcategories recursively
        foreach ($categories as &$category) {
            $category['article_count'] = $this->categoryModel->countEnabledArticlesRecursive($category['id']);
        }
        
        // Get counts for enabled/total
        $totalCategories = $this->categoryModel->countAll();
        $enabledCategories = $this->categoryModel->countEnabled();
        $totalArticles = $this->articleModel->countAll();
        $enabledArticles = $this->articleModel->countEnabled();
        
        $data = [
            'pageTitle' => 'Dashboard',
            'pageCategory' => 'Dashboard',
            'user' => $user,
            'categories' => $categories,
            'articles' => $articles,
            'totalCategories' => $totalCategories,
            'enabledCategories' => $enabledCategories,
            'totalArticles' => $totalArticles,
            'enabledArticles' => $enabledArticles,
            'message' => $this->getMessage(),
            'error' => $this->getError()
        ];
        
        return $this->layout('admin', 'admin/dashboard', $data);
    }
    
    public function settings()
    {
        $user = $this->getUser();
        
        if ($this->isPost()) {
            return $this->updateSettings();
        }
        
        $data = [
            'pageTitle' => 'Settings',
            'pageCategory' => 'Settings',
            'user' => $user,
            'message' => $this->getMessage(),
            'error' => $this->getError()
        ];
        
        return $this->layout('admin', 'admin/settings', $data);
    }
    
    public function updateSettings()
    {
        $apiEnabled = $this->rawInput('api_enabled');
        if ($apiEnabled !== 'enabled' && $apiEnabled !== 'disabled') {
            return $this->redirectWithError('/admin/settings', 'Choose whether the API is enabled or disabled.');
        }

        $cors = trim((string)$this->rawInput('api_cors_origin'));
        if ($cors === '') {
            $cors = '*';
        }
        if (strlen($cors) > 255 || !$this->isAllowedCorsOrigin($cors)) {
            return $this->redirectWithError('/admin/settings', 'CORS origin must be * or a single http or https origin, such as https://app.example.com.');
        }

        $visibility = $this->rawInput('kb_visibility');
        if ($visibility !== 'public' && $visibility !== 'private') {
            return $this->redirectWithError('/admin/settings', 'Choose a valid knowledge base visibility.');
        }

        $maintenance = $this->rawInput('maintenance_mode');
        if ($maintenance !== 'enabled' && $maintenance !== 'disabled') {
            return $this->redirectWithError('/admin/settings', 'Choose whether maintenance mode is enabled or disabled.');
        }

        $generateKey = isset($_POST['generate_api_key']) && (string)$_POST['generate_api_key'] === '1';
        if ($generateKey) {
            $apiKey = bin2hex(random_bytes(32));
        } else {
            $apiKey = trim((string)$this->rawInput('api_key'));
            if ($apiKey !== '' && !preg_match('/\A[A-Za-z0-9._~-]{16,128}\z/', $apiKey)) {
                return $this->redirectWithError('/admin/settings', 'The API key must be 16 to 128 characters and may contain only letters, numbers, periods, underscores, tildes, and hyphens. Leave it blank when no key is required.');
            }
        }

        $settings = [
            'site_name' => $this->input('site_name'),
            'site_color' => $this->input('site_color'),
            'site_logo' => $this->input('site_logo'),
            'kb_visibility' => $visibility,
            'maintenance_mode' => $maintenance,
            'maintenance_message' => $this->input('maintenance_message'),
            'api_enabled' => $apiEnabled,
            'api_key' => $apiKey,
            'api_cors_origin' => $cors
        ];
        
        $this->settingModel->setMultiple($settings);
        
        // Log settings update. The API key itself is not written to the log.
        $user = $this->getUser();
        Log::logAction($user['id'], $user['email'], 'updated', 'settings');

        $message = $generateKey
            ? 'Settings saved. A new API key was generated. Copy it from the field below.'
            : 'Settings saved successfully.';
        
        return $this->redirectWithMessage('/admin/settings', $message);
    }

    private function isAllowedCorsOrigin($origin)
    {
        if ($origin === '*') {
            return true;
        }

        return (bool)preg_match('#\Ahttps?://[A-Za-z0-9.-]+(?::[0-9]{1,5})?\z#', $origin);
    }
    
    public function logs()
    {
        $user = $this->getUser();
        
        // Only admin can view logs
        if ($user['group'] !== 'admin') {
            return $this->show403();
        }
        
        $logModel = new Log();
        $page = $this->input('page', 1);
        $perPage = 50;
        
        $logs = $logModel->getAllLogs($page, $perPage);
        $totalLogs = $logModel->countLogs();
        $totalPages = ceil($totalLogs / $perPage);
        
        $data = [
            'pageTitle' => 'Activity Log',
            'pageCategory' => 'Logs',
            'user' => $user,
            'logs' => $logs,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalLogs' => $totalLogs,
            'message' => $this->getMessage(),
            'error' => $this->getError()
        ];
        
        return $this->layout('admin', 'admin/logs', $data);
    }

}
