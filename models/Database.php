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
            $path = parse_url($requestUri, PHP_URL_PATH) ?? '';
            if (preg_match('#/api(/|$)#', $path)) {
                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
                die('{"error":"The request could not be completed."}');
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
     * True when a 2.x database is present and a later upgrade has not been applied.
     * That is the attachments table (2.1.0) or the read-only API settings (2.2.0).
     */
    public function needsUpgrade() {
        if (!empty($this->checkCoreTables())) {
            return false;
        }

        $missing = $this->checkRequiredTables();
        if (!empty($missing)) {
            $coreMissing = array_diff($missing, $this->upgradeTables());
            return empty($coreMissing);
        }

        return !empty($this->missingApiSettings());
    }

    /**
     * Settings added in 2.2.0. The API stays off until an administrator enables it.
     */
    public static function apiSettingDefaults() {
        return [
            'api_enabled' => 'disabled',
            'api_key' => '',
            'api_cors_origin' => '*'
        ];
    }

    public function missingApiSettings() {
        $missing = [];

        try {
            $pdo = $this->connect();
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE name = ?");

            foreach (self::apiSettingDefaults() as $name => $value) {
                $stmt->execute([$name]);
                if ((int)$stmt->fetchColumn() === 0) {
                    $missing[] = $name;
                }
            }

            return $missing;
        } catch (PDOException $e) {
            return array_keys(self::apiSettingDefaults());
        }
    }

    /**
     * Insert any missing API settings. Existing values are left as they are.
     */
    public function ensureApiSettings() {
        $pdo = $this->connect();
        $stmt = $pdo->prepare("INSERT IGNORE INTO settings (name, value) VALUES (?, ?)");

        foreach (self::apiSettingDefaults() as $name => $value) {
            $stmt->execute([$name, $value]);
        }

        return true;
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
