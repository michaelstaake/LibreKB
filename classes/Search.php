<?php
class Search extends Database {
    public function search() {
        $query = $this->query;
        $query = "%$query%";
        $sql = "SELECT id, title, slug FROM articles WHERE (title LIKE :query OR content LIKE :query) AND `status` = 'enabled'";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindParam(':query', $query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}