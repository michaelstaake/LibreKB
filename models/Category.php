<?php

class Category extends Model
{
    protected $table = 'categories';
    
    public function getBySlug($slug)
    {
        return $this->findBy('slug', $slug);
    }
    
    public function getEnabledBySlug($slug)
    {
        $sql = "SELECT * FROM {$this->table} WHERE slug = :slug AND status = 'enabled'";
        return $this->fetchOne($sql, ['slug' => $slug]);
    }
    
    public function getAllEnabled()
    {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'enabled' ORDER BY `order`+0 ASC";
        return $this->fetchAll($sql);
    }
    
    public function getTopLevel()
    {
        $sql = "SELECT * FROM {$this->table} WHERE parent IS NULL ORDER BY `order`+0 ASC";
        return $this->fetchAll($sql);
    }
    
    public function getEnabledTopLevel()
    {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'enabled' AND parent IS NULL ORDER BY `order`+0 ASC";
        return $this->fetchAll($sql);
    }
    
    public function getWithParent($parentId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE parent = :parentId ORDER BY `order`+0 ASC";
        return $this->fetchAll($sql, ['parentId' => $parentId]);
    }
    
    public function getEnabledWithParent($parentId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE parent = :parentId AND status = 'enabled' ORDER BY `order`+0 ASC";
        return $this->fetchAll($sql, ['parentId' => $parentId]);
    }
    
    public function countWithParent($parentId)
    {
        return $this->count('parent = :parentId', ['parentId' => $parentId]);
    }
    
    public function getAllWithArticleCount()
    {
        $sql = "SELECT c.*, COUNT(a.id) as article_count 
                FROM {$this->table} c 
                LEFT JOIN articles a ON c.id = a.category 
                GROUP BY c.id 
                ORDER BY c.`order`+0 ASC";
        return $this->fetchAll($sql);
    }
    
    public function getParentOptions($excludeId = null)
    {
        $sql = "SELECT id, name FROM {$this->table} WHERE parent IS NULL";
        if ($excludeId) {
            $sql .= " AND id != :excludeId";
        }
        $sql .= " ORDER BY name ASC";
        
        $params = $excludeId ? ['excludeId' => $excludeId] : [];
        return $this->fetchAll($sql, $params);
    }
    
    public function getAllWithArticles()
    {
        $sql = "SELECT c.*, COUNT(a.id) as article_count 
                FROM {$this->table} c 
                LEFT JOIN articles a ON c.id = a.category 
                GROUP BY c.id 
                ORDER BY c.parent IS NULL DESC, c.`order`+0 ASC";
        return $this->fetchAll($sql);
    }
    
    public function countAll()
    {
        return $this->count();
    }
    
    public function countEnabled()
    {
        return $this->count('status = :status', ['status' => 'enabled']);
    }
    
    /**
     * Get all subcategory IDs recursively for a given category
     */
    public function getAllSubcategoryIds($categoryId)
    {
        $subcategoryIds = [$categoryId];
        $directSubcategories = $this->getWithParent($categoryId);
        
        foreach ($directSubcategories as $subcategory) {
            $subcategoryIds = array_merge($subcategoryIds, $this->getAllSubcategoryIds($subcategory['id']));
        }
        
        return $subcategoryIds;
    }
    
    /**
     * Count enabled articles in a category and all its subcategories recursively
     */
    public function countEnabledArticlesRecursive($categoryId)
    {
        $subcategoryIds = $this->getAllSubcategoryIds($categoryId);
        $placeholders = str_repeat('?,', count($subcategoryIds) - 1) . '?';
        
        $sql = "SELECT COUNT(*) as count FROM articles WHERE category IN ({$placeholders}) AND status = 'enabled'";
        $result = $this->fetchOne($sql, $subcategoryIds);
        
        return $result ? (int)$result['count'] : 0;
    }
    
    public function upgradeDBto130()
    {
        try {
            $sql = "ALTER TABLE {$this->table} ADD COLUMN parent INT(6) AFTER id";
            $this->query($sql);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}
