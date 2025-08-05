<?php

class Controller
{
    protected $data = [];
    
    protected function view($template, $data = [])
    {
        $this->data = array_merge($this->data, $data);
        
        // Extract data to variables
        extract($this->data);
        
        // Include the view file
        $viewFile = VIEWS_PATH . '/' . $template . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new Exception("View file not found: " . $viewFile);
        }
    }
    
    protected function layout($layout, $contentView, $data = [])
    {
        $this->data = array_merge($this->data, $data);
        
        // Automatically add user data for admin layout
        if ($layout === 'admin' && !isset($this->data['user'])) {
            $this->data['user'] = $this->getUser();
        }
        
        // Automatically add site name for admin layout
        if ($layout === 'admin' && !isset($this->data['siteName'])) {
            $this->data['siteName'] = $this->getSetting('site_name', 'LibreKB');
        }
        
        // First, render the content view
        ob_start();
        extract($this->data);
        $contentFile = VIEWS_PATH . '/' . $contentView . '.php';
        if (file_exists($contentFile)) {
            include $contentFile;
        } else {
            throw new Exception("Content view file not found: " . $contentFile);
        }
        $content = ob_get_clean();
        
        // Now add the rendered content to the data
        $this->data['content'] = $content;
        
        // Extract data to variables again (including the rendered content)
        extract($this->data);
        
        // Include the layout file
        $layoutFile = VIEWS_PATH . '/layouts/' . $layout . '.php';
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            throw new Exception("Layout file not found: " . $layoutFile);
        }
    }
    
    protected function redirect($url, $status = 302)
    {
        http_response_code($status);
        header('Location: ' . $url);
        exit;
    }
    
    protected function json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    protected function show404()
    {
        http_response_code(404);
        
        // Get site name for the 404 page
        $siteName = $this->getSetting('site_name', 'Knowledge Base');
        
        // Include the 404 view with site name
        $viewFile = VIEWS_PATH . '/404.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            // Fallback if 404.php doesn't exist
            echo "404 - Page Not Found";
        }
        exit;
    }
    
    public function show403()
    {
        http_response_code(403);
        
        // Get site name for the 403 page
        $siteName = $this->getSetting('site_name', 'Knowledge Base');
        
        // Include the 403 view with site name
        $viewFile = VIEWS_PATH . '/403.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            // Fallback if 403.php doesn't exist
            echo "403 - Access Forbidden";
        }
        exit;
    }
    
    protected function input($key, $default = null)
    {
        if (isset($_POST[$key])) {
            return htmlspecialchars($_POST[$key], ENT_QUOTES, 'UTF-8');
        }
        if (isset($_GET[$key])) {
            return htmlspecialchars($_GET[$key], ENT_QUOTES, 'UTF-8');
        }
        return $default;
    }
    
    protected function rawInput($key, $default = null)
    {
        if (isset($_POST[$key])) {
            return trim($_POST[$key]);
        }
        if (isset($_GET[$key])) {
            return trim($_GET[$key]);
        }
        return $default;
    }
    
    protected function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    protected function isGet()
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
    
    protected function getUser()
    {
        if (isset($_SESSION['user_id'])) {
            try {
                $user = new User();
                return $user->getUser($_SESSION['user_id']);
            } catch (Exception $e) {
                // If there's a database error, return null
                if (strpos($e->getMessage(), "doesn't exist") !== false) {
                    $this->handleDatabaseError();
                }
                return null;
            }
        }
        return null;
    }
    
    protected function getSetting($name, $default = '')
    {
        try {
            $setting = new Setting();
            $value = $setting->getValue($name);
            return $value ?: $default;
        } catch (Exception $e) {
            // If there's a database error, return default
            if (strpos($e->getMessage(), "doesn't exist") !== false) {
                $this->handleDatabaseError();
            }
            return $default;
        }
    }
    
    protected function setSetting($name, $value)
    {
        try {
            $setting = new Setting();
            $setting->setValue($name, $value);
        } catch (Exception $e) {
            // If there's a database error, handle it
            if (strpos($e->getMessage(), "doesn't exist") !== false) {
                $this->handleDatabaseError();
            }
            throw $e;
        }
    }
    
    private function handleDatabaseError()
    {
        $database = new Database();
        $missingTables = $database->checkRequiredTables();
        
        if (!empty($missingTables)) {
            include ROOT_PATH . '/views/database-error.php';
            exit;
        }
    }
    
    protected function checkKnowledgeBaseAccess()
    {
        try {
            $kbVisibility = $this->getSetting('kb_visibility', 'public');
            if ($kbVisibility === 'private' && !isset($_SESSION['user_id'])) {
                $this->setError('Please log in to access the knowledge base.');
                return $this->redirect('/login');
            }
            return true;
        } catch (Exception $e) {
            // If there's a database error while checking access, 
            // assume public access to avoid blocking users
            return true;
        }
    }
    
    // Session-based message handling
    protected function setMessage($message)
    {
        $_SESSION['message'] = $message;
    }
    
    protected function setError($error)
    {
        $_SESSION['error'] = $error;
    }
    
    protected function getMessage()
    {
        $message = $_SESSION['message'] ?? null;
        unset($_SESSION['message']);
        return $message;
    }
    
    protected function getError()
    {
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['error']);
        return $error;
    }
    
    protected function redirectWithMessage($url, $message)
    {
        $this->setMessage($message);
        return $this->redirect($url);
    }
    
    protected function redirectWithError($url, $error)
    {
        $this->setError($error);
        return $this->redirect($url);
    }
}
