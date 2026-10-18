<?php

class Attachment extends Model
{
    protected $table = 'attachments';

    public function getByArticleId($articleId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE article_id = :articleId ORDER BY id ASC";
        return $this->fetchAll($sql, ['articleId' => $articleId]);
    }

    public function findForArticle($id, $articleId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id AND article_id = :articleId";
        return $this->fetchOne($sql, ['id' => $id, 'articleId' => $articleId]);
    }
}
