<?php

class ArticleController extends Controller
{
    private $settingModel;
    private $categoryModel;
    private $articleModel;
    
    public function __construct()
    {
        $this->settingModel = new Setting();
        $this->categoryModel = new Category();
        $this->articleModel = new Article();
    }
    
    public function show($slug)
    {
        // Check maintenance mode
        $maintenanceMode = $this->settingModel->getValue('maintenance_mode');
        if ($maintenanceMode === 'enabled') {
            return $this->showMaintenance();
        }
        
        // Check knowledge base access
        $this->checkKnowledgeBaseAccess();
        
        // Get article
        $article = $this->articleModel->getEnabledBySlug($slug);
        if (!$article) {
            return $this->show404();
        }
        $article['content'] = HtmlSanitizer::clean($article['content'] ?? '');
        
        // Get site settings
        $siteName = $this->settingModel->getValue('site_name') ?: 'Knowledge Base';
        
        // Get category
        $category = $this->categoryModel->find($article['category']);
        if (!$category) {
            return $this->show404();
        }
        
        // Check if category hierarchy is enabled
        if (!$this->categoryModel->isCategoryHierarchyEnabled($category['id'])) {
            return $this->show404();
        }
        
        $attachmentModel = new Attachment();

        $data = [
            'pageTitle' => $article['title'],
            'siteName' => $siteName,
            'article' => $article,
            'category' => $category,
            'ancestors' => $this->categoryModel->getAncestors($category['id']),
            'attachments' => $attachmentModel->getByArticleId($article['id']),
            'message' => $this->getMessage(),
            'error' => $this->getError()
        ];
        
        return $this->layout('main', 'article', $data);
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
    
    // Admin methods
    public function adminIndex()
    {
        return $this->index();
    }
    
    public function index()
    {
        $articles = $this->articleModel->getAllWithCategoryName();
        
        $data = [
            'pageTitle' => 'Articles',
            'pageCategory' => 'Articles',
            'articles' => $articles,
            'message' => $this->getMessage(),
            'error' => $this->getError()
        ];
        
        return $this->layout('admin', 'admin/articles', $data);
    }
    
    public function adminShow($id)
    {
        return $this->edit($id);
    }
    
    public function create()
    {
        if ($this->isPost()) {
            return $this->store();
        }

        $categories = $this->categoryModel->getIndentedOptions();
        
        // Get the selected category from URL parameter
        $selectedCategoryId = $this->input('category');

        $data = [
            'pageTitle' => 'Create Article',
            'pageCategory' => 'Articles',
            'categories' => $categories,
            'selectedCategoryId' => $selectedCategoryId,
            'message' => $this->getMessage(),
            'error' => $this->getError()
        ];

        return $this->layout('admin', 'admin/articles-create', $data);
    }    public function store()
    {
        if ($this->uploadWasDropped()) {
            $this->setError('The upload is larger than the server allows. Increase post_max_size and upload_max_filesize, then try again.');
            return $this->redirect('/admin/articles/create');
        }

        $title = $this->rawInput('title');
        $slug = $this->generateSlug($title);
        $content = HtmlSanitizer::clean($this->rawInput('content'));
        $category = $this->input('category');
        $status = $this->input('status');
        $orderInput = $this->input('order');
        
        // Use manual order if provided, otherwise auto-assign
        $order = (!empty($orderInput) && is_numeric($orderInput)) ? (int)$orderInput : $this->articleModel->getNextOrder($category);
        
        $articleData = [
            'number' => $this->articleModel->getNextNumber(),
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'category' => $category,
            'status' => $status,
            'order' => $order,
            'featured' => 0,
            'created' => date('Y-m-d H:i:s'),
            'updated' => date('Y-m-d H:i:s')
        ];
        
        $articleId = $this->articleModel->create($articleData);
        if ($articleId) {
            // Log article creation
            $user = $this->getUser();
            Log::logAction($user['id'], $user['email'], 'created', 'article');

            $uploadErrors = $this->saveAttachments($articleId, $user);
            if (!empty($uploadErrors)) {
                $this->setError('Article saved, but some attachments were not added: ' . implode(' ', $uploadErrors));
                return $this->redirect('/admin/articles/' . $articleId);
            }
            
            $this->setMessage('Article created successfully.');
            return $this->redirect('/admin');
        } else {
            $this->setError('Error creating article. Please try again.');
            return $this->redirect('/admin/articles/create');
        }
    }
    
    public function edit($id)
    {
        $article = $this->articleModel->find($id);
        
        if (!$article) {
            $this->setError('Article not found.');
            return $this->redirect('/admin');
        }
        $article['content'] = HtmlSanitizer::clean($article['content'] ?? '');
        
        if ($this->isPost()) {
            return $this->update($id);
        }
        
        $categories = $this->categoryModel->getIndentedOptions();
        $attachmentModel = new Attachment();
        
        $data = [
            'pageTitle' => 'Edit Article',
            'pageCategory' => 'Articles',
            'article' => $article,
            'categories' => $categories,
            'attachments' => $attachmentModel->getByArticleId($article['id']),
            'message' => $this->getMessage(),
            'error' => $this->getError()
        ];
        
        return $this->layout('admin', 'admin/articles-edit', $data);
    }
    
    public function update($id)
    {
        if ($this->uploadWasDropped()) {
            $this->setError('The upload is larger than the server allows. Increase post_max_size and upload_max_filesize, then try again.');
            return $this->redirect('/admin/articles/' . $id);
        }

        $title = $this->rawInput('title');
        $slug = $this->generateSlug($title, $id);
        $content = HtmlSanitizer::clean($this->rawInput('content'));
        $category = $this->input('category');
        $status = $this->input('status');
        $order = $this->input('order');
        
        $updateData = [
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'category' => $category,
            'status' => $status,
            'order' => $order,
            'updated' => date('Y-m-d H:i:s')
        ];
        
        if ($this->articleModel->update($id, $updateData)) {
            // Log article update
            $user = $this->getUser();
            Log::logAction($user['id'], $user['email'], 'updated', 'article');

            $uploadErrors = $this->saveAttachments($id, $user);
            if (!empty($uploadErrors)) {
                $this->setError('Article saved, but some attachments were not added: ' . implode(' ', $uploadErrors));
                return $this->redirect('/admin/articles/' . $id);
            }
            
            $this->setMessage('Article updated successfully.');
            return $this->redirect('/admin');
        } else {
            $this->setError('Error updating article. Please try again.');
            return $this->redirect('/admin/articles/' . $id);
        }
    }
    
    public function deleteAttachment($id, $attachmentId)
    {
        $article = $this->articleModel->find($id);
        if (!$article) {
            $this->setError('Article not found.');
            return $this->redirect('/admin');
        }

        $attachmentModel = new Attachment();
        $attachment = $attachmentModel->findForArticle($attachmentId, $id);
        if (!$attachment) {
            $this->setError('Attachment not found.');
            return $this->redirect('/admin/articles/' . $id);
        }

        $storage = new FileStorage();
        $storage->delete($attachment['stored_name'], 'attachments');
        $attachmentModel->delete($attachment['id']);

        $user = $this->getUser();
        Log::logAction($user['id'], $user['email'], 'deleted', 'attachment');

        $this->setMessage('Attachment removed.');
        return $this->redirect('/admin/articles/' . $id);
    }

    public function delete($id)
    {
        $attachmentModel = new Attachment();
        $attachments = $attachmentModel->getByArticleId($id);

        if ($this->articleModel->delete($id)) {
            $storage = new FileStorage();
            foreach ($attachments as $attachment) {
                $storage->delete($attachment['stored_name'], 'attachments');
                $attachmentModel->delete($attachment['id']);
            }
            // Log article deletion
            $user = $this->getUser();
            Log::logAction($user['id'], $user['email'], 'deleted', 'article');
            
            $this->setMessage('Article deleted successfully.');
            return $this->redirect('/admin');
        } else {
            $this->setError('Error deleting article. Please try again.');
            return $this->redirect('/admin');
        }
    }
    
    /**
     * PHP discards the body when it exceeds post_max_size, leaving both
     * $_POST and $_FILES empty while Content-Length is still set.
     */
    private function uploadWasDropped()
    {
        $length = isset($_SERVER['CONTENT_LENGTH']) ? (int)$_SERVER['CONTENT_LENGTH'] : 0;
        return $length > 0 && empty($_POST) && empty($_FILES);
    }

    private function saveAttachments($articleId, $user)
    {
        $files = FileStorage::normalizeUploadedFiles('attachments');
        if (empty($files)) {
            return [];
        }

        if (count($files) > FileStorage::MAX_FILES) {
            return ['You can upload up to ' . FileStorage::MAX_FILES . ' files at a time.'];
        }

        $storage = new FileStorage();
        $attachmentModel = new Attachment();
        $errors = [];
        $saved = 0;

        foreach ($files as $file) {
            try {
                $stored = $storage->store($file, 'attachments');
                $attachmentModel->create([
                    'article_id' => $articleId,
                    'original_name' => $stored['original_name'],
                    'stored_name' => $stored['stored_name'],
                    'extension' => $stored['extension'],
                    'mime_type' => $stored['mime_type'],
                    'size' => $stored['size'],
                    'created' => date('Y-m-d H:i:s')
                ]);
                $saved++;
            } catch (Exception $e) {
                $label = isset($file['name']) ? basename(str_replace('\\', '/', $file['name'])) : 'A file';
                $errors[] = $label . ': ' . $e->getMessage();
            }
        }

        if ($saved > 0) {
            Log::logAction($user['id'], $user['email'], 'uploaded', 'attachment');
        }

        return $errors;
    }

    private function generateSlug($title, $excludeId = null)
    {
        // First, convert HTML entities back to characters
        $title = html_entity_decode($title, ENT_QUOTES, 'UTF-8');
        
        // Remove special characters and replace with spaces, then with hyphens
        $slug = preg_replace('/[^A-Za-z0-9\s-]/', '', $title);
        $slug = preg_replace('/\s+/', '-', $slug);
        $slug = strtolower(trim($slug, '-'));
        
        // Remove multiple consecutive hyphens
        $slug = preg_replace('/-+/', '-', $slug);
        
        // Check if slug exists
        $counter = 1;
        $originalSlug = $slug;
        
        if ($excludeId) {
            // When editing, exclude the current article from the check
            while ($this->articleModel->getBySlugExcludingId($slug, $excludeId)) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
        } else {
            // When creating, check all articles
            while ($this->articleModel->getBySlug($slug)) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
        }
        
        return $slug;
    }
}
