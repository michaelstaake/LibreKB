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
            $_SESSION['user_id'] = $user['id'];
            
            // Log successful login
            Log::logAction($user['id'], $user['email'], 'logged in', 'system');
            
            // Redirect based on user group
            if ($user['group'] === 'admin' || $user['group'] === 'manager') {
                return $this->redirect('/admin?action=updateCheck');
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
        
        session_destroy();
        return $this->redirect('/login');
    }
    
    public function dashboard()
    {
        $current = new Version();
        if ($current->channel === 'release') {
            $updateStatus = $this->checkForUpdates();
            if (strpos($updateStatus, 'update check complete - update yes') !== false) {
                $this->setError('A new version of LibreKB is available. Go to librekb.com to learn more and get the update.');
            } elseif (strpos($updateStatus, 'update check config invalid') !== false) {
                $this->setError('Update checks config invalid must be yes or no.');
            } else {
                $this->setError('Update check failed.');
            }
        }        
        
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
        $settings = [
            'site_name' => $this->input('site_name'),
            'site_color' => $this->input('site_color'),
            'site_logo' => $this->input('site_logo'),
            'kb_visibility' => $this->input('kb_visibility'),
            'maintenance_mode' => $this->input('maintenance_mode'),
            'maintenance_message' => $this->input('maintenance_message')
        ];
        
        $this->settingModel->setMultiple($settings);
        
        // Log settings update
        $user = $this->getUser();
        Log::logAction($user['id'], $user['email'], 'updated', 'settings');
        
        return $this->redirectWithMessage('/admin/settings', 'Settings saved successfully.');
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
    
    private function checkForUpdates()
    {
        $config = new Config();
        if ($config->updateCheck === 'yes') {
            try {
                $current = new Version();
                $latestJson = file_get_contents('https://librekb.com/latest.php?version=' . $current->version);
                if ($latestJson === false) {
                    return 'update check failed';
                }
                $latestData = json_decode($latestJson, true);
                if ($latestData && isset($latestData['version'])) {
                    $latestVersion = $latestData['version'];
                    if ($current->version != $latestVersion) {
                        return 'update check complete - update yes';
                    } else {
                        return 'update check complete - update no';
                    }
                } else {
                    return 'update check failed';
                }
            } catch (Exception $e) {
                return 'update check failed';
            }
        } else if ($config->updateCheck === 'no') {
            return 'update check disabled';
        } else {
            return 'update check config invalid';
        }
    }
}
