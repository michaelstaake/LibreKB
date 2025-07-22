<?php

class InstallController extends Controller
{
    public function checkInstallable() {
        try {
            $database = new Database();
            $missingTables = $database->checkRequiredTables();
            
            // If no tables are missing, check if users table has any users
            if (empty($missingTables)) {
                $conn = $database->connect();
                $stmt = $conn->query("SELECT COUNT(*) FROM users");
                $userCount = $stmt->fetchColumn();
                return $userCount == 0; // Installable if no users exist
            }
            
            // If tables are missing, it's installable
            return true;
        } catch (Exception $e) {
            // If there's any error, assume it's installable
            return true;
        }
    }
    
    public function checkDatabaseConnection() {
        try {
            $database = new Database();
            $conn = $database->connect();
            
            // Test the connection with a simple query
            $stmt = $conn->query("SELECT 1");
            $result = $stmt->fetch();
            
            return [
                'success' => true,
                'message' => 'Database connection successful',
                'error' => null
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Database connection failed',
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function index()
    {
        // Check if system is already installed
        if (!$this->checkInstallable()) {
            return $this->show404();
        }
        
        if ($this->isPost()) {
            return $this->install();
        }
        
        // Check database connection
        $dbConnectionStatus = $this->checkDatabaseConnection();
        
        $data = [
            'pageTitle' => 'LibreKB Installer',
            'message' => $this->getMessage(),
            'error' => $this->getError(),
            'dbConnection' => $dbConnectionStatus
        ];
        
        return $this->view('install', $data);
    }
    
    public function install()
    {
        // Check if system is already installed
        if (!$this->checkInstallable()) {
            return $this->show404();
        }
        
        $email = $this->input('email');
        $password = $this->input('password');
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $database = new Database();
            
            // First check database connection
            $dbConnectionStatus = $this->checkDatabaseConnection();
            if (!$dbConnectionStatus['success']) {
                $this->setError('Database connection failed: ' . $dbConnectionStatus['error']);
                return $this->redirect('/install');
            }
            
            $conn = $database->connect();
            
            // Create tables
            $this->createTables($conn);
            
            // Insert settings
            $this->insertSettings($conn);
            
            // Create admin user
            $this->createAdminUser($conn, $email, $hashedPassword);
            
            $this->setMessage('Installation completed successfully.');
            return $this->redirect('/admin');
            
        } catch (Exception $e) {
            $this->setError('Installation failed: ' . $e->getMessage());
            return $this->redirect('/install');
        }
    }
    
    private function createTables($conn)
    {
        $createTablesQuery = "
        CREATE TABLE users (
            `id` INT(6) AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL,
            `email` VARCHAR(255) NOT NULL,
            `password` VARCHAR(255) NOT NULL,
            `group` VARCHAR(255) NOT NULL,
            `status` VARCHAR(255) NOT NULL,
            `timezone` VARCHAR(255) NOT NULL,
            `pw_reset_key` VARCHAR(255),
            `pw_reset_exp` VARCHAR(255),
            `created` VARCHAR(255)
        );
        
        CREATE TABLE settings (
            `name` VARCHAR(255) PRIMARY KEY,
            `value` VARCHAR(255)
        );
        
        CREATE TABLE articles (
            `id` INT(6) AUTO_INCREMENT PRIMARY KEY,
            `number` INT(6) NOT NULL,
            `title` VARCHAR(255) NOT NULL,
            `slug` VARCHAR(255) NOT NULL,
            `category` VARCHAR(255) NOT NULL,
            `content` LONGTEXT NOT NULL,
            `order` VARCHAR(255) NOT NULL,
            `status` VARCHAR(255) NOT NULL,
            `featured` INT(6) NOT NULL,
            `created` VARCHAR(255) NOT NULL,
            `updated` VARCHAR(255) NOT NULL
        );
        
        CREATE TABLE categories (
            `id` INT(6) AUTO_INCREMENT PRIMARY KEY,
            `parent` INT(6),
            `name` VARCHAR(255) NOT NULL,
            `slug` VARCHAR(255) NOT NULL,
            `description` LONGTEXT,
            `icon` VARCHAR(255),
            `order` VARCHAR(255) NOT NULL,
            `status` VARCHAR(255) NOT NULL,
            `created` VARCHAR(255) NOT NULL,
            `updated` VARCHAR(255) NOT NULL
        );
        
        CREATE TABLE log (
            `id` INT(6) AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT(6) NOT NULL,
            `user_email` VARCHAR(255) NOT NULL,
            `user_ip` VARCHAR(255) NOT NULL,
            `performed_action` VARCHAR(255) NOT NULL,
            `on_what` VARCHAR(255) NOT NULL,
            `when` VARCHAR(255) NOT NULL
        );";
        
        $conn->exec($createTablesQuery);
    }
    
    private function insertSettings($conn)
    {
        $settingsData = [
            ['name' => 'site_name', 'value' => ''],
            ['name' => 'maintenance_mode', 'value' => ''],
            ['name' => 'maintenance_message', 'value' => ''],
            ['name' => 'site_color', 'value' => ''],
            ['name' => 'site_logo', 'value' => ''],
            ['name' => 'kb_visibility', 'value' => 'public']
        ];
        
        $insertSettingsQuery = "INSERT INTO settings (name, value) VALUES (:name, :value)";
        $stmt = $conn->prepare($insertSettingsQuery);
        
        foreach ($settingsData as $data) {
            $stmt->execute($data);
        }
    }
    
    private function createAdminUser($conn, $email, $password)
    {
        $createUserQuery = "INSERT INTO users (`name`, `email`, `password`, `group`, `status`, `timezone`, `created`) VALUES (:name, :email, :password, 'admin', 'enabled', 'timezone', :created)";
        $userData = [
            'name' => 'Admin',
            'email' => $email,
            'password' => $password,
            'created' => date('Y-m-d H:i:s')
        ];
        
        $stmt = $conn->prepare($createUserQuery);
        $stmt->execute($userData);
    }
}
