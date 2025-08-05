<?php

class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    
    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }
    
    protected function query($sql, $params = [])
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            // Check if it's a table doesn't exist error
            if (strpos($e->getMessage(), "doesn't exist") !== false || 
                strpos($e->getMessage(), "Table") !== false) {
                // Redirect to database error page
                $this->handleMissingTable($e);
            }
            throw $e;
        }
    }
    
    private function handleMissingTable($exception)
    {
        // Extract table name from error message if possible
        $message = $exception->getMessage();
        $database = new Database();
        $missingTables = $database->checkRequiredTables();
        
        if (!empty($missingTables)) {
            include ROOT_PATH . '/views/database-error.php';
            exit;
        }
    }
    
    protected function fetchAll($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    protected function fetchOne($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    protected function execute($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    public function find($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        return $this->fetchOne($sql, ['id' => $id]);
    }
    
    public function findBy($column, $value)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = :value";
        return $this->fetchOne($sql, ['value' => $value]);
    }
    
    public function all()
    {
        $sql = "SELECT * FROM {$this->table}";
        return $this->fetchAll($sql);
    }
    
    public function where($conditions, $params = [])
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$conditions}";
        return $this->fetchAll($sql, $params);
    }
    
    public function create($data)
    {
        $columns = array_keys($data);
        $placeholders = ':' . implode(', :', $columns);
        
        // Escape column names with backticks to handle reserved keywords
        $escapedColumns = array_map(function($col) { return "`{$col}`"; }, $columns);
        $columnList = implode(', ', $escapedColumns);
        
        $sql = "INSERT INTO {$this->table} ({$columnList}) VALUES ({$placeholders})";
        
        $stmt = $this->query($sql, $data);
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data)
    {
        $setClause = [];
        foreach (array_keys($data) as $column) {
            // Escape column names with backticks to handle reserved keywords
            $setClause[] = "`{$column}` = :{$column}";
        }
        $setClause = implode(', ', $setClause);
        
        $data['id'] = $id;
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = :id";
        
        try {
            $stmt = $this->query($sql, $data);
            // Return true if query executed successfully, regardless of affected rows
            // This handles the case where no changes were made (0 affected rows)
            // which is not an error but a valid scenario when the same data is submitted
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        return $this->execute($sql, ['id' => $id]);
    }
    
    public function count($conditions = '', $params = [])
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        if ($conditions) {
            $sql .= " WHERE {$conditions}";
        }
        
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn();
    }
}
