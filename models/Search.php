<?php

class Search extends Model
{
    public function search($query)
    {
        $searchTerm = "%{$query}%";
        $sql = "SELECT id, title, slug FROM articles WHERE (title LIKE :query OR content LIKE :query) AND status = 'enabled'";
        return $this->fetchAll($sql, ['query' => $searchTerm]);
    }
}
