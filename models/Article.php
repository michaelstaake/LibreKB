<?php

class Article extends Model
{
    protected $table = 'articles';
    
    public function getBySlug($slug)
    {
        return $this->findBy('slug', $slug);
    }
    
    public function getBySlugExcludingId($slug, $excludeId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE slug = :slug AND id != :excludeId";
        return $this->fetchOne($sql, ['slug' => $slug, 'excludeId' => $excludeId]);
    }
    
    public function getEnabledBySlug($slug)
    {
        $sql = "SELECT * FROM {$this->table} WHERE slug = :slug AND status = 'enabled'";
        return $this->fetchOne($sql, ['slug' => $slug]);
    }
    
    public function getByCategoryId($categoryId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE category = :categoryId ORDER BY `order`+0 ASC";
        return $this->fetchAll($sql, ['categoryId' => $categoryId]);
    }
    
    public function getEnabledByCategoryId($categoryId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE category = :categoryId AND status = 'enabled' ORDER BY `order`+0 ASC";
        return $this->fetchAll($sql, ['categoryId' => $categoryId]);
    }
    
    public function countByCategoryId($categoryId)
    {
        return $this->count('category = :categoryId', ['categoryId' => $categoryId]);
    }
    
    public function countEnabledByCategoryId($categoryId)
    {
        return $this->count('category = :categoryId AND status = \'enabled\'', ['categoryId' => $categoryId]);
    }
    
    public function search($query)
    {
        $searchTerm = "%{$query}%";
        $sql = "SELECT id, title, slug FROM {$this->table} WHERE (title LIKE :query OR content LIKE :query) AND status = 'enabled'";
        return $this->fetchAll($sql, ['query' => $searchTerm]);
    }
    
    public function getAllWithCategoryName()
    {
        $sql = "SELECT a.*, c.name as category_name 
                FROM {$this->table} a 
                LEFT JOIN categories c ON a.category = c.id 
                ORDER BY a.created DESC";
        return $this->fetchAll($sql);
    }
    
    public function getNextOrder($categoryId)
    {
        $sql = "SELECT MAX(`order`+0) as max_order FROM {$this->table} WHERE category = :categoryId";
        $result = $this->fetchOne($sql, ['categoryId' => $categoryId]);
        return ($result['max_order'] ?? 0) + 1;
    }
    
    public function getNextNumber()
    {
        $sql = "SELECT MAX(number) as max_number FROM {$this->table}";
        $result = $this->fetchOne($sql);
        return ($result['max_number'] ?? 0) + 1;
    }
    
    public function countAll()
    {
        return $this->count();
    }
    
    public function countEnabled()
    {
        return $this->count('status = :status', ['status' => 'enabled']);
    }
}
