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
            $requestUri = $_SERVER['REQUEST_URI'] ?? '';
            if (strpos($requestUri, '/install') !== false || strpos($requestUri, '/upgrade') !== false) {
                throw $e; // Re-throw for installer and upgrade to handle
            }
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    public function coreTables() {
        return ['users', 'settings', 'articles', 'categories', 'log'];
    }

    /**
     * Tables added after 2.0.1. Existing installs gain these through /upgrade
     * without changes to the tables they already have.
     */
    public function upgradeTables() {
        return ['attachments'];
    }

    public function requiredTables() {
        return array_merge($this->coreTables(), $this->upgradeTables());
    }

    public function checkRequiredTables() {
        return $this->missingTables($this->requiredTables());
    }

    public function checkCoreTables() {
        return $this->missingTables($this->coreTables());
    }

    public function hasRequiredTables() {
        return empty($this->checkRequiredTables());
    }

    /**
     * True when a 2.x database is present and only newer tables are missing.
     */
    public function needsUpgrade() {
        $missing = $this->checkRequiredTables();
        if (empty($missing)) {
            return false;
        }

        $coreMissing = array_diff($missing, $this->upgradeTables());
        return empty($coreMissing);
    }

    /**
     * CREATE TABLE statement for article attachments.
     * Shared by the installer and the 2.1.0 upgrade so the definitions cannot drift.
     */
    public static function attachmentsTableSql($ifNotExists = false) {
        $create = $ifNotExists ? 'CREATE TABLE IF NOT EXISTS' : 'CREATE TABLE';
        return $create . " attachments (
            `id` INT(6) AUTO_INCREMENT PRIMARY KEY,
            `article_id` INT(6) NOT NULL,
            `original_name` VARCHAR(255) NOT NULL,
            `stored_name` VARCHAR(255) NOT NULL,
            `extension` VARCHAR(20) NOT NULL,
            `mime_type` VARCHAR(255) NOT NULL,
            `size` INT(11) NOT NULL,
            `created` VARCHAR(255) NOT NULL,
            KEY `article_id` (`article_id`)
        )";
    }

    /**
     * Add the 2.1.0 attachments table. Does not alter or delete existing rows.
     */
    public function createAttachmentsTable() {
        $pdo = $this->connect();
        $pdo->exec(self::attachmentsTableSql(true));
        return true;
    }

    private function missingTables($tables) {
        $missingTables = [];

        try {
            $pdo = $this->connect();

            foreach ($tables as $table) {
                $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
                $stmt->execute([$table]);

                if ($stmt->rowCount() === 0) {
                    $missingTables[] = $table;
                }
            }

            return $missingTables;
        } catch (PDOException $e) {
            return $tables;
        }
    }
}

?>
