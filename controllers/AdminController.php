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
    
    public function settingsIndex()
    {
        return $this->redirect('/admin/settings/general');
    }

    public function settings($section)
    {
        if (!$this->isSettingsSection($section)) {
            return $this->show404();
        }

        $user = $this->getUser();
        $invalid = $_SESSION['settings_invalid'] ?? null;
        unset($_SESSION['settings_invalid']);
        $invalidField = '';
        if (is_array($invalid) && ($invalid['section'] ?? '') === $section) {
            $invalidField = (string)($invalid['field'] ?? '');
        }

        $data = [
            'pageTitle' => 'Settings',
            'pageCategory' => 'Settings',
            'user' => $user,
            'section' => $section,
            'sections' => $this->settingsNavigation(),
            'invalidField' => $invalidField,
            'message' => $this->getMessage(),
            'error' => $this->getError()
        ];

        return $this->layout('admin', 'admin/settings', $data);
    }

    public function updateSettings($section)
    {
        if (!$this->isSettingsSection($section)) {
            return $this->show404();
        }

        $user = $this->getUser();
        if (!$user || $user['group'] !== 'admin') {
            return $this->show403();
        }

        if ($section === 'general') {
            return $this->updateGeneralSettings($user);
        }
        if ($section === 'appearance') {
            return $this->updateAppearanceSettings($user);
        }
        if ($section === 'api') {
            return $this->updateApiSettings($user);
        }
        if ($section === 'maintenance') {
            return $this->updateMaintenanceSettings($user);
        }
        if ($section === 'editor') {
            return $this->updateEditorSettings($user);
        }

        return $this->show404();
    }

    private function updateGeneralSettings($user)
    {
        $visibility = $this->rawInput('kb_visibility');
        if ($visibility !== 'public' && $visibility !== 'private') {
            return $this->settingsError('general', 'kb_visibility', 'Choose a valid knowledge base visibility.');
        }

        $this->settingModel->setMultiple([
            'site_name' => $this->input('site_name'),
            'kb_visibility' => $visibility
        ]);

        return $this->settingsSaved($user, 'general', 'Settings saved successfully.');
    }

    private function updateAppearanceSettings($user)
    {
        $customCss = (string)$this->rawInput('custom_css');
        $cssError = CustomCss::rejectionMessage($customCss);
        if ($cssError !== '') {
            return $this->settingsError('appearance', 'custom_css', $cssError);
        }

        $this->settingModel->setMultiple([
            'site_color' => $this->input('site_color'),
            'site_logo' => $this->input('site_logo'),
            'custom_css' => $customCss
        ]);

        return $this->settingsSaved($user, 'appearance', 'Settings saved successfully.');
    }

    private function updateApiSettings($user)
    {
        $apiEnabled = $this->rawInput('api_enabled');
        if ($apiEnabled !== 'enabled' && $apiEnabled !== 'disabled') {
            return $this->settingsError('api', 'api_enabled', 'Choose whether the API is enabled or disabled.');
        }

        $cors = trim((string)$this->rawInput('api_cors_origin'));
        if ($cors === '') {
            $cors = '*';
        }
        if (strlen($cors) > 255 || !$this->isAllowedCorsOrigin($cors)) {
            return $this->settingsError('api', 'api_cors_origin', 'CORS origin must be * or a single http or https origin, such as https://app.example.com.');
        }

        $generateKey = isset($_POST['generate_api_key']) && (string)$_POST['generate_api_key'] === '1';
        if ($generateKey) {
            $apiKey = bin2hex(random_bytes(32));
        } else {
            $apiKey = trim((string)$this->rawInput('api_key'));
            if ($apiKey !== '' && !preg_match('/\A[A-Za-z0-9._~-]{16,128}\z/', $apiKey)) {
                return $this->settingsError('api', 'api_key', 'The API key must be 16 to 128 characters and may contain only letters, numbers, periods, underscores, tildes, and hyphens. Leave it blank when no key is required.');
            }
        }

        $this->settingModel->setMultiple([
            'api_enabled' => $apiEnabled,
            'api_key' => $apiKey,
            'api_cors_origin' => $cors
        ]);

        $message = $generateKey
            ? 'Settings saved. A new API key was generated. Copy it from the field below.'
            : 'Settings saved successfully.';

        return $this->settingsSaved($user, 'api', $message);
    }

    private function updateMaintenanceSettings($user)
    {
        $maintenance = $this->rawInput('maintenance_mode');
        if ($maintenance !== 'enabled' && $maintenance !== 'disabled') {
            return $this->settingsError('maintenance', 'maintenance_mode', 'Choose whether maintenance mode is enabled or disabled.');
        }

        $this->settingModel->setMultiple([
            'maintenance_mode' => $maintenance,
            'maintenance_message' => $this->input('maintenance_message')
        ]);

        return $this->settingsSaved($user, 'maintenance', 'Settings saved successfully.');
    }

    private function updateEditorSettings($user)
    {
        $posted = $_POST['toolbar'] ?? [];
        $buttons = EditorToolbar::sanitize($posted);
        if (empty($buttons)) {
            return $this->settingsError('editor', 'toolbar', 'Choose at least one toolbar button.');
        }

        $this->settingModel->setValue('editor_toolbar', EditorToolbar::storedValue($buttons));
        return $this->settingsSaved($user, 'editor', 'Settings saved successfully.');
    }

    private function settingsSaved($user, $section, $message)
    {
        // The API key itself is not written to the log.
        Log::logAction($user['id'], $user['email'], 'updated', 'settings');
        return $this->redirectWithMessage('/admin/settings/' . $section, $message);
    }

    private function settingsError($section, $field, $message)
    {
        $_SESSION['settings_invalid'] = [
            'section' => $section,
            'field' => $field
        ];
        return $this->redirectWithError('/admin/settings/' . $section, $message);
    }

    private function isSettingsSection($section)
    {
        return in_array($section, ['general', 'appearance', 'editor', 'api', 'maintenance'], true);
    }

    private function settingsNavigation()
    {
        $siteName = $this->getSetting('site_name', 'Knowledge Base');
        $visibility = $this->getSetting('kb_visibility', 'public') === 'private' ? 'Private' : 'Public';

        $color = trim((string)$this->settingModel->getValue('site_color'));
        $logo = trim((string)$this->settingModel->getValue('site_logo'));
        $customCss = (string)$this->settingModel->getValue('custom_css');
        if (CustomCss::printable($customCss) !== '') {
            $appearance = 'Custom CSS on';
        } elseif ($color !== '') {
            $appearance = $color;
        } elseif ($logo !== '') {
            $appearance = 'Logo set';
        } else {
            $appearance = 'Default color';
        }

        $apiEnabled = $this->getSetting('api_enabled', 'disabled') === 'enabled';
        $maintenanceOn = $this->getSetting('maintenance_mode') === 'enabled';

        return [
            'general' => [
                'label' => 'General',
                'status' => $siteName . ' · ' . $visibility
            ],
            'appearance' => [
                'label' => 'Appearance',
                'status' => $appearance
            ],
            'editor' => [
                'label' => 'Editor',
                'status' => EditorToolbar::navigationStatus((string)$this->settingModel->getValue('editor_toolbar'))
            ],
            'api' => [
                'label' => 'API',
                'status' => $apiEnabled ? 'Enabled' : 'Disabled'
            ],
            'maintenance' => [
                'label' => 'Maintenance',
                'status' => $maintenanceOn ? 'On' : 'Off'
            ]
        ];
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
