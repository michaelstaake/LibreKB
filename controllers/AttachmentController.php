<?php

class AttachmentController extends Controller
{
    private $settingModel;
    private $categoryModel;
    private $articleModel;
    private $attachmentModel;

    public function __construct()
    {
        $this->settingModel = new Setting();
        $this->categoryModel = new Category();
        $this->articleModel = new Article();
        $this->attachmentModel = new Attachment();
    }

    public function download($id)
    {
        $attachment = $this->attachmentModel->find($id);
        if (!$attachment) {
            return $this->show404();
        }

        $article = $this->articleModel->find($attachment['article_id']);
        if (!$article) {
            return $this->show404();
        }

        $category = $this->categoryModel->find($article['category']);
        $access = $this->accessDecision($article, $category);

        if ($access === 'maintenance') {
            return $this->showMaintenance();
        }
        if ($access === 'login') {
            $this->setError('Please log in to access the knowledge base.');
            return $this->redirect('/login');
        }
        if ($access !== 'ok') {
            return $this->show404();
        }

        $storage = new FileStorage();
        if (!$storage->isSafeStoredName($attachment['stored_name'])) {
            return $this->show404();
        }

        $path = $storage->absolutePath($attachment['stored_name'], 'attachments');
        if (!$path || !is_file($path)) {
            return $this->show404();
        }

        $downloadName = str_replace(["\r", "\n", '"', '\\'], '', (string)$attachment['original_name']);
        if ($downloadName === '') {
            $downloadName = 'download.' . preg_replace('/[^a-z0-9]/', '', strtolower((string)$attachment['extension']));
        }
        $mime = $storage->mimeForExtension($attachment['extension']);

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
        header('X-Content-Type-Options: nosniff');
        header('Content-Security-Policy: default-src \'none\'');
        header('Cache-Control: private, no-store');
        readfile($path);
        exit;
    }

    /**
     * Public downloads follow the same rules as the article page.
     * Admins and managers can still download while editing hidden articles.
     */
    private function accessDecision($article, $category)
    {
        $user = $this->getUser();
        $isStaff = $user && ($user['group'] === 'admin' || $user['group'] === 'manager');
        if ($isStaff) {
            return 'ok';
        }

        $maintenanceMode = $this->settingModel->getValue('maintenance_mode');
        if ($maintenanceMode === 'enabled') {
            return 'maintenance';
        }

        $kbVisibility = $this->getSetting('kb_visibility', 'public');
        if ($kbVisibility === 'private' && !$user) {
            return 'login';
        }

        if ($article['status'] !== 'enabled' || !$category) {
            return 'missing';
        }

        if (!$this->categoryModel->isCategoryHierarchyEnabled($category['id'])) {
            return 'missing';
        }

        return 'ok';
    }

    private function showMaintenance()
    {
        $siteName = $this->settingModel->getValue('site_name') ?: 'Knowledge Base';
        $maintenanceMessage = $this->settingModel->getValue('maintenance_message') ?:
            'The Knowledge Base is undergoing maintenance, please check back later.';

        $data = [
            'pageTitle' => 'Maintenance',
            'siteName' => $siteName,
            'maintenanceMessage' => $maintenanceMessage,
            'message' => $this->getMessage(),
            'error' => $this->getError()
        ];

        return $this->layout('main', 'maintenance', $data);
    }
}
