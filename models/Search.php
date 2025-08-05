<?php

class Search extends Model
{
    public function search($query)
    {
        $searchTerm = "%{$query}%";
        $results = [];
        
        // Get disabled category IDs to exclude from search
        $categoryModel = new Category();
        $disabledCategoryIds = $categoryModel->getAllDisabledCategoryIds();
        
        // Build exclusion clause for disabled categories
        $excludeClause = '';
        $excludeParams = [];
        if (!empty($disabledCategoryIds)) {
            $placeholders = str_repeat('?,', count($disabledCategoryIds) - 1) . '?';
            $excludeClause = " AND a.category NOT IN ({$placeholders})";
            $excludeParams = $disabledCategoryIds;
        }
        
        // Search in articles with category information, excluding disabled categories
        $articleSql = "SELECT a.id, a.title, a.slug, 'article' as type, c.name as category_name, c.slug as category_slug 
                       FROM articles a 
                       LEFT JOIN categories c ON a.category = c.id 
                       WHERE (a.title LIKE ? OR a.content LIKE ?) AND a.status = 'enabled'" . $excludeClause;
        
        $articleParams = array_merge([$searchTerm, $searchTerm], $excludeParams);
        $articles = $this->fetchAll($articleSql, $articleParams);
        
        // Build exclusion clause for category search
        $categoryExcludeClause = '';
        $categoryExcludeParams = [];
        if (!empty($disabledCategoryIds)) {
            $placeholders = str_repeat('?,', count($disabledCategoryIds) - 1) . '?';
            $categoryExcludeClause = " AND id NOT IN ({$placeholders})";
            $categoryExcludeParams = $disabledCategoryIds;
        }
        
        // Search in categories, excluding disabled ones
        $categorySql = "SELECT id, name as title, slug, 'category' as type, NULL as category_name, NULL as category_slug 
                        FROM categories 
                        WHERE (name LIKE ? OR description LIKE ?) AND status = 'enabled'" . $categoryExcludeClause;
        
        $categoryParams = array_merge([$searchTerm, $searchTerm], $categoryExcludeParams);
        $categories = $this->fetchAll($categorySql, $categoryParams);
        
        // Combine results
        $results = array_merge($articles, $categories);
        
        // Sort results by type (categories first) and then by title
        usort($results, function($a, $b) {
            if ($a['type'] !== $b['type']) {
                return $a['type'] === 'category' ? -1 : 1;
            }
            return strcasecmp($a['title'], $b['title']);
        });
        
        return $results;
    }
}
