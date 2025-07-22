<?php

class Search extends Model
{
    public function search($query)
    {
        $searchTerm = "%{$query}%";
        $results = [];
        
        // Search in articles with category information
        $articleSql = "SELECT a.id, a.title, a.slug, 'article' as type, c.name as category_name, c.slug as category_slug 
                       FROM articles a 
                       LEFT JOIN categories c ON a.category = c.id 
                       WHERE (a.title LIKE :query OR a.content LIKE :query) AND a.status = 'enabled'";
        $articles = $this->fetchAll($articleSql, ['query' => $searchTerm]);
        
        // Search in categories
        $categorySql = "SELECT id, name as title, slug, 'category' as type, NULL as category_name, NULL as category_slug 
                        FROM categories 
                        WHERE (name LIKE :query OR description LIKE :query) AND status = 'enabled'";
        $categories = $this->fetchAll($categorySql, ['query' => $searchTerm]);
        
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
