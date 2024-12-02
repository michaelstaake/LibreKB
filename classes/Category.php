<?php

class Category extends Database {

    public function getAllCategories() {
        $query = "SELECT * FROM categories ORDER BY `order`+0 ASC";
        $stmt = $this->connect()->prepare($query);
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $categories;
    }

    public function getAllCategoriesEnabled() {
        $query = "SELECT * FROM categories WHERE `status` = 'enabled' ORDER BY `order`+0 ASC";
        $stmt = $this->connect()->prepare($query);
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $categories;
    }

    public function getCategory($id) {
        $query = "SELECT * FROM categories WHERE id = :id";
        $stmt = $this->connect()->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($category) {
            return $category;
        } else {
            return false;
        }
    }

    public function getCategoryBySlug($slug) {
        $query = "SELECT * FROM categories WHERE slug = :slug AND `status` = 'enabled'";
        $stmt = $this->connect()->prepare($query);
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($category) {
            return $category;
        } else {
            return false;
        }
    }

    public function createCategory() {
        $name = $this->name;
        $slug = strtolower(str_replace(' ', '-', $name));
        $description = $this->description;
        $icon = "folder";
        $order = $this->order;
        $status = $this->status;
        $created = date('Y-m-d H:i:s');
        $updated = $created;

        $query = "INSERT INTO categories (`name`, `slug`, `description`, `icon`, `order`, `status`, `created`, `updated`) VALUES (:name, :slug, :description, :icon, :order, :status, :created, :updated)";

        try {
            $stmt = $this->connect()->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':icon', $icon);
            $stmt->bindParam(':order', $order);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':created', $created);
            $stmt->bindParam(':updated', $updated);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updateCategory() {
        $id = $this->id;
        $name = $this->name;
        $slug = strtolower(str_replace(' ', '-', $name));
        $description = $this->description;
        $icon = "folder";
        $order = $this->order;
        $status = $this->status;
        $updated = date('Y-m-d H:i:s');

        $query = "UPDATE categories SET `name` = :name, `slug` = :slug, `description` = :description, `icon` = :icon, `order` = :order, `status` = :status, `updated` = :updated WHERE `id` = :id";

        try {
            $stmt = $this->connect()->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':icon', $icon);
            $stmt->bindParam(':order', $order);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':updated', $updated);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            //die("Error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteCategory() {
        $id = $this->id;

        $query = "SELECT COUNT(*) FROM articles WHERE category = :id";
        $stmt = $this->connect()->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            return false;
        } else {
            $query = "DELETE FROM categories WHERE id = :id";
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

    // This is only for 1.2.2 to 1.3.0 to add parent column to categories table

    public function upgradeDBto130() {
        $query = "ALTER TABLE categories ADD COLUMN parent INT DEFAULT NULL";
        try {
            $stmt = $this->connect()->prepare($query);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}