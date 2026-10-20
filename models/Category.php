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
        $sql = "SELECT * FROM {$this->table} WHERE status = 'enabled' ORDER BY `order`+0 ASC, name ASC, id ASC";
        return $this->fetchAll($sql);
    }
    
    public function getTopLevel()
    {
        $sql = "SELECT * FROM {$this->table} WHERE parent IS NULL ORDER BY `order`+0 ASC, name ASC, id ASC";
        return $this->fetchAll($sql);
    }
    
    public function getEnabledTopLevel()
    {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'enabled' AND parent IS NULL ORDER BY `order`+0 ASC, name ASC, id ASC";
        $categories = $this->fetchAll($sql);
        
        // Filter out categories where hierarchy is not fully enabled
        $enabledCategories = [];
        foreach ($categories as $category) {
            if ($this->isCategoryHierarchyEnabled($category['id'])) {
                $enabledCategories[] = $category;
            }
        }
        
        return $enabledCategories;
    }
    
    public function getWithParent($parentId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE parent = :parentId ORDER BY `order`+0 ASC, name ASC, id ASC";
        return $this->fetchAll($sql, ['parentId' => $parentId]);
    }
    
    public function getEnabledWithParent($parentId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE parent = :parentId AND status = 'enabled' ORDER BY `order`+0 ASC, name ASC, id ASC";
        $categories = $this->fetchAll($sql, ['parentId' => $parentId]);
        
        // Filter out categories where hierarchy is not fully enabled
        $enabledCategories = [];
        foreach ($categories as $category) {
            if ($this->isCategoryHierarchyEnabled($category['id'])) {
                $enabledCategories[] = $category;
            }
        }
        
        return $enabledCategories;
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
                ORDER BY c.`order`+0 ASC, c.name ASC, c.id ASC";
        return $this->fetchAll($sql);
    }
    
    /**
     * Parent choices for the category form, in tree order.
     * The category being edited and every category under it are omitted so a
     * category cannot be placed inside itself.
     */
    public function getParentOptions($excludeId = null)
    {
        $excludeIds = [];
        if ($excludeId) {
            foreach ($this->getAllSubcategoryIds($excludeId) as $id) {
                $excludeIds[(int)$id] = true;
            }
        }

        $options = [];
        $used = [];
        $this->appendTreeOptions($this->allInListOrder(), null, 0, $excludeIds, $options, $used);
        return $options;
    }

    /**
     * Every category in tree order, including nested categories, for article assignment.
     */
    public function getIndentedOptions()
    {
        $options = [];
        $used = [];
        $this->appendTreeOptions($this->allInListOrder(), null, 0, [], $options, $used);
        return $options;
    }

    /**
     * Ancestors from the top of the tree down to, but not including, this category.
     */
    public function getAncestors($categoryId)
    {
        $ancestors = [];
        $seen = [];
        $current = $this->find($categoryId);
        $guard = 0;

        while ($current && !empty($current['parent']) && $guard < 100) {
            $guard++;
            $parentId = (int)$current['parent'];
            if (isset($seen[$parentId])) {
                break;
            }
            $seen[$parentId] = true;

            $parent = $this->find($parentId);
            if (!$parent) {
                break;
            }

            array_unshift($ancestors, $parent);
            $current = $parent;
        }

        return $ancestors;
    }

    /**
     * A parent is valid when it exists and is not this category or one of its descendants.
     */
    public function canBeParent($categoryId, $parentId)
    {
        $categoryId = (int)$categoryId;
        $parentId = (int)$parentId;

        if ($parentId <= 0 || !$this->find($parentId)) {
            return false;
        }

        if ($categoryId > 0) {
            foreach ($this->getAllSubcategoryIds($categoryId) as $id) {
                if ((int)$id === $parentId) {
                    return false;
                }
            }
        }

        return true;
    }
    
    public function getAllWithArticles()
    {
        $sql = "SELECT c.*, COUNT(a.id) as article_count 
                FROM {$this->table} c 
                LEFT JOIN articles a ON c.id = a.category 
                GROUP BY c.id 
                ORDER BY c.parent IS NULL DESC, c.`order`+0 ASC, c.name ASC, c.id ASC";
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
    public function getAllSubcategoryIds($categoryId, &$visited = null)
    {
        if ($visited === null) {
            $visited = [];
        }

        $categoryId = (int)$categoryId;
        if ($categoryId <= 0 || isset($visited[$categoryId])) {
            return [];
        }
        $visited[$categoryId] = true;

        $subcategoryIds = [$categoryId];
        $directSubcategories = $this->getWithParent($categoryId);

        foreach ($directSubcategories as $subcategory) {
            $subcategoryIds = array_merge(
                $subcategoryIds,
                $this->getAllSubcategoryIds($subcategory['id'], $visited)
            );
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
    
    /**
     * Check if a category and its entire parent hierarchy is enabled
     */
    public function isCategoryHierarchyEnabled($categoryId, &$visited = null)
    {
        if ($visited === null) {
            $visited = [];
        }

        $categoryId = (int)$categoryId;
        if ($categoryId <= 0 || isset($visited[$categoryId])) {
            return false;
        }
        $visited[$categoryId] = true;

        $category = $this->find($categoryId);
        if (!$category || $category['status'] !== 'enabled') {
            return false;
        }

        // Check parent hierarchy recursively
        if ($category['parent']) {
            return $this->isCategoryHierarchyEnabled($category['parent'], $visited);
        }

        return true;
    }

    /**
     * Every category in list order: order number, then name, then id.
     * Name uses the column collation, which is case-insensitive.
     */
    private function allInListOrder()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY `order`+0 ASC, name ASC, id ASC";
        return $this->fetchAll($sql);
    }

    /**
     * Flatten categories into a depth-first list. Orphans (parent missing) are
     * appended so they can still be selected.
     */
    private function appendTreeOptions($categories, $parentId, $depth, $excludeIds, &$options, &$used)
    {
        $children = [];
        foreach ($categories as $category) {
            $categoryParent = $category['parent'];
            $isChild = $parentId === null
                ? ($categoryParent === null || $categoryParent === '')
                : ((string)$categoryParent === (string)$parentId);

            if ($isChild) {
                $children[] = $category;
            }
        }

        usort($children, function ($a, $b) {
            $left = $a['order'] + 0;
            $right = $b['order'] + 0;
            if ($left == $right) {
                // allInListOrder() already applied the column collation, then id.
                return 0;
            }
            return ($left < $right) ? -1 : 1;
        });

        foreach ($children as $child) {
            $childId = (int)$child['id'];
            if (isset($used[$childId])) {
                continue;
            }
            $used[$childId] = true;

            if (!isset($excludeIds[$childId])) {
                $options[] = [
                    'id' => $child['id'],
                    'name' => $child['name'],
                    'depth' => $depth,
                    'parent' => $child['parent']
                ];
                $this->appendTreeOptions($categories, $childId, $depth + 1, $excludeIds, $options, $used);
            }
        }

        // After the real tree is walked from the top, pick up categories whose parent is missing.
        if ($parentId === null) {
            foreach ($categories as $category) {
                $categoryId = (int)$category['id'];
                if (isset($used[$categoryId]) || isset($excludeIds[$categoryId])) {
                    continue;
                }
                $used[$categoryId] = true;
                $options[] = [
                    'id' => $category['id'],
                    'name' => $category['name'],
                    'depth' => 0,
                    'parent' => $category['parent']
                ];
                $this->appendTreeOptions($categories, $categoryId, 1, $excludeIds, $options, $used);
            }
        }
    }
    
    /**
     * Get all disabled category IDs (including those with disabled parents)
     */
    public function getAllDisabledCategoryIds()
    {
        $allCategories = $this->all();
        $disabledIds = [];
        
        foreach ($allCategories as $category) {
            if (!$this->isCategoryHierarchyEnabled($category['id'])) {
                $disabledIds[] = $category['id'];
            }
        }
        
        return $disabledIds;
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
