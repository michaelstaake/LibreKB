<?php
class Article extends Database {
    public function getNumberOfArticlesByCategoryId($categoryId) {
        $query = "SELECT COUNT(*) FROM articles WHERE category = :categoryId";
        $statement = $this->connect()->prepare($query);
        $statement->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchColumn();
    }
    public function getNumberOfArticlesEnabledByCategoryId($categoryId) {
        $query = "SELECT COUNT(*) FROM articles WHERE category = :categoryId AND `status` = 'enabled'";
        $statement = $this->connect()->prepare($query);
        $statement->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchColumn();
    }
    public function getArticlesByCategoryId($categoryId) {
        $query = "SELECT * FROM articles WHERE category = :categoryId ORDER BY `order`+0 ASC";
        $statement = $this->connect()->prepare($query);
        $statement->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getArticlesEnabledByCategoryId($categoryId) {
        $query = "SELECT * FROM articles WHERE category = :categoryId AND `status` = 'enabled' ORDER BY `order`+0 ASC";
        $statement = $this->connect()->prepare($query);
        $statement->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getArticle($id) {
        $query = "SELECT * FROM articles WHERE id = :id";
        $stmt = $this->connect()->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $article = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($article) {
            return $article;
        } else {
            return false;
        }
    }
    public function getArticleBySlug($slug) {
        $query = "SELECT * FROM articles WHERE slug = :slug AND `status` = 'enabled'";
        $stmt = $this->connect()->prepare($query);
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        $article = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($article) {
            return $article;
        } else {
            return false;
        }
    }
    public function createArticle() {
        $number = mt_rand(100000, 999999);
        $title = $this->title;
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9 ]/', '', $title));
        $slug = str_replace(' ', '-', $slug);
        $category = $this->category;
        $content = $this->content;
        $order = $this->order;
        $status = $this->status;
        $featured = "0";
        $created = date('Y-m-d H:i:s');
        $updated = $created;
        $query = "INSERT INTO articles (`number`, `title`, `slug`, `category`, `content`, `order`, `status`, `featured`, `created`, `updated`) VALUES (:number, :title, :slug, :category, :content, :order, :status, :featured, :created, :updated)";
        try {
            $stmt = $this->connect()->prepare($query);
            $stmt->bindParam(':number', $number);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':category', $category);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':order', $order);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':featured', $featured);
            $stmt->bindParam(':created', $created);
            $stmt->bindParam(':updated', $updated);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }
    public function updateArticle() {
        $id = $this->id;
        $title = $this->title;
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9 ]/', '', $title));
        $slug = str_replace(' ', '-', $slug);
        $category = $this->category;
        $content = $this->content;
        $order = $this->order;
        $status = $this->status;
        $updated = date('Y-m-d H:i:s');
        $query = "UPDATE articles SET title = :title, slug = :slug, category = :category, content = :content, `order` = :order, status = :status, updated = :updated WHERE id = :id";
        try {
            $stmt = $this->connect()->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':category', $category);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':order', $order);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':updated', $updated);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }
    public function deleteArticle() {
        $id = $this->id;
        $query = "DELETE FROM articles WHERE id = :id";
        try {
            $stmt = $this->connect()->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            //die("Error: " . $e->getMessage());
            return false;
        }
    }
}
?>