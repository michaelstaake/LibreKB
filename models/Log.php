<?php

class Log extends Model {
    protected $table = 'log';
    
    /**
     * Log an action performed by a user
     * 
     * @param int $userId - The ID of the user performing the action
     * @param string $userEmail - The email of the user performing the action
     * @param string $performedAction - The action performed (created, updated, deleted, etc.)
     * @param string $onWhat - The type of entity acted upon (article, category, user, setting, profile)
     * @param string $userIp - The IP address of the user
     * @return bool - True if logged successfully, false otherwise
     */
    public static function logAction($userId, $userEmail, $performedAction, $onWhat, $userIp = null) {
        try {
            $log = new self();
            
            // Get user IP if not provided
            if ($userIp === null) {
                $userIp = self::getUserIP();
            }
            
            $data = [
                'user_id' => $userId,
                'user_email' => $userEmail,
                'performed_action' => $performedAction,
                'on_what' => $onWhat,
                'when' => date('Y-m-d H:i:s'),
                'user_ip' => $userIp
            ];
            
            return $log->create($data);
        } catch (Exception $e) {
            // Log errors but don't break the application
            error_log("Failed to log action: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get the real IP address of the user
     * Handles cases where the application is behind a proxy, load balancer, or Cloudflare
     * 
     * @return string - The user's IP address
     */
    private static function getUserIP() {
        // Check for Cloudflare connecting IP first (most reliable when using Cloudflare)
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        // Check for IP from shared internet
        elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        // Check for IP passed from proxy
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Can contain multiple IPs, get the first one
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }
        // Check for IP from remote address
        elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }
        
        return 'unknown';
    }
    
    /**
     * Get all logs with pagination
     * 
     * @param int $page - Page number (1-based)
     * @param int $perPage - Number of logs per page
     * @return array - Array of log entries
     */
    public function getAllLogs($page = 1, $perPage = 50) {
        $offset = ($page - 1) * $perPage;
        
        // Cast to integers to ensure proper SQL syntax
        $limit = (int)$perPage;
        $offset = (int)$offset;
        
        $sql = "SELECT l.*, u.name as user_name 
                FROM {$this->table} l 
                LEFT JOIN users u ON l.user_id = u.id 
                ORDER BY l.when DESC 
                LIMIT {$limit} OFFSET {$offset}";
        
        return $this->fetchAll($sql);
    }
    
    /**
     * Get logs for a specific user
     * 
     * @param int $userId - The user ID to get logs for
     * @param int $limit - Maximum number of logs to return
     * @return array - Array of log entries for the user
     */
    public function getUserLogs($userId, $limit = 100) {
        $limit = (int)$limit;
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id 
                ORDER BY when DESC 
                LIMIT {$limit}";
        
        $params = [
            ':user_id' => $userId
        ];
        
        return $this->fetchAll($sql, $params);
    }
    
    /**
     * Get logs for a specific action type
     * 
     * @param string $action - The action to filter by
     * @param int $limit - Maximum number of logs to return
     * @return array - Array of log entries for the action
     */
    public function getActionLogs($action, $limit = 100) {
        $limit = (int)$limit;
        
        $sql = "SELECT l.*, u.name as user_name 
                FROM {$this->table} l 
                LEFT JOIN users u ON l.user_id = u.id 
                WHERE l.performed_action = :action 
                ORDER BY l.when DESC 
                LIMIT {$limit}";
        
        $params = [
            ':action' => $action
        ];
        
        return $this->fetchAll($sql, $params);
    }
    
    /**
     * Get recent logs (last 24 hours by default)
     * 
     * @param int $hours - Number of hours to look back
     * @return array - Array of recent log entries
     */
    public function getRecentLogs($hours = 24) {
        $sql = "SELECT l.*, u.name as user_name 
                FROM {$this->table} l 
                LEFT JOIN users u ON l.user_id = u.id 
                WHERE l.when >= DATE_SUB(NOW(), INTERVAL :hours HOUR) 
                ORDER BY l.when DESC";
        
        $params = [':hours' => $hours];
        
        return $this->fetchAll($sql, $params);
    }
    
    /**
     * Count total logs
     * 
     * @return int - Total number of log entries
     */
    public function countLogs() {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $stmt = $this->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }
}

?>
