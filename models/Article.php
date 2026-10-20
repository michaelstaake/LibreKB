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
        $sql = "SELECT * FROM {$this->table} WHERE category = :categoryId ORDER BY `order`+0 ASC, title ASC, id ASC";
        return $this->fetchAll($sql, ['categoryId' => $categoryId]);
    }
    
    public function getEnabledByCategoryId($categoryId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE category = :categoryId AND status = 'enabled' ORDER BY `order`+0 ASC, title ASC, id ASC";
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

    /**
     * Enabled articles whose category is still public.
     * $disabledCategoryIds comes from Category::getAllDisabledCategoryIds().
     * Limit and offset are cast to integers and bounded here.
     */
    public function searchPublic($query, array $disabledCategoryIds, $limit, $offset)
    {
        $limit = (int)$limit;
        $offset = (int)$offset;
        if ($limit < 1 || $limit > 50 || $offset < 0) {
            return [];
        }

        list($where, $params) = $this->publicSearchWhere($query, $disabledCategoryIds);
        $sql = "SELECT a.id, a.title, a.slug, a.category
                FROM {$this->table} a
                INNER JOIN categories c ON a.category = c.id
                WHERE {$where}
                ORDER BY a.title ASC
                LIMIT {$limit} OFFSET {$offset}";

        return $this->fetchAll($sql, $params);
    }

    public function countPublicSearch($query, array $disabledCategoryIds)
    {
        list($where, $params) = $this->publicSearchWhere($query, $disabledCategoryIds);
        $sql = "SELECT COUNT(*) FROM {$this->table} a
                INNER JOIN categories c ON a.category = c.id
                WHERE {$where}";
        $stmt = $this->query($sql, $params);
        return (int)$stmt->fetchColumn();
    }

    private function publicSearchWhere($query, array $disabledCategoryIds)
    {
        $searchTerm = '%' . $this->escapeLike((string)$query) . '%';
        $params = [$searchTerm, $searchTerm];
        $exclude = '';

        $ids = [];
        foreach ($disabledCategoryIds as $id) {
            $id = (int)$id;
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $exclude = " AND a.category NOT IN ({$placeholders})";
            $params = array_merge($params, $ids);
        }

        $where = "(a.title LIKE ? ESCAPE '\\\\' OR a.content LIKE ? ESCAPE '\\\\') AND a.status = 'enabled'" . $exclude;
        return [$where, $params];
    }

    private function escapeLike($value)
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
    
    public function getAllWithCategoryName()
    {
        $sql = "SELECT a.*, c.name as category_name 
                FROM {$this->table} a 
                LEFT JOIN categories c ON a.category = c.id 
                ORDER BY a.`order`+0 ASC, a.title ASC, a.id ASC";
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
