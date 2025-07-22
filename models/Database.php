<?php

class Database extends Config {

    public function connect() {
        try {
            $dsn = "mysql:host={$this->db_host};dbname={$this->db_name}";
            $pdo = new PDO($dsn, $this->db_user, $this->db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            // For installer, we want to catch and handle errors gracefully
            // Check if we're in installer context
            if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/install') !== false) {
                throw $e; // Re-throw for installer to handle
            }
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    public function checkRequiredTables() {
        $requiredTables = ['users', 'settings', 'articles', 'categories'];
        $missingTables = [];
        
        try {
            $pdo = $this->connect();
            
            foreach ($requiredTables as $table) {
                $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
                $stmt->execute([$table]);
                
                if ($stmt->rowCount() === 0) {
                    $missingTables[] = $table;
                }
            }
            
            return $missingTables;
        } catch (PDOException $e) {
            // If we can't even check tables, assume all are missing
            return $requiredTables;
        }
    }
    
    public function hasRequiredTables() {
        return empty($this->checkRequiredTables());
    }
}

?>
