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
        
        // Get parent category if exists
        $parentCategory = null;
        if ($category && $category['parent']) {
            $parentCategory = $this->categoryModel->find($category['parent']);
        }
        
        $data = [
            'pageTitle' => $article['title'],
            'siteName' => $siteName,
            'article' => $article,
            'category' => $category,
            'parentCategory' => $parentCategory,
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

        $categories = $this->categoryModel->all();
        
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
        $title = $this->rawInput('title');
        $slug = $this->generateSlug($title);
        $content = $this->rawInput('content');
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
        
        if ($this->articleModel->create($articleData)) {
            // Log article creation
            $user = $this->getUser();
            Log::logAction($user['id'], $user['email'], 'created', 'article');
            
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
        
        if ($this->isPost()) {
            return $this->update($id);
        }
        
        $categories = $this->categoryModel->all();
        
        $data = [
            'pageTitle' => 'Edit Article',
            'pageCategory' => 'Articles',
            'article' => $article,
            'categories' => $categories,
            'message' => $this->getMessage(),
            'error' => $this->getError()
        ];
        
        return $this->layout('admin', 'admin/articles-edit', $data);
    }
    
    public function update($id)
    {
        $title = $this->rawInput('title');
        $slug = $this->generateSlug($title, $id);
        $content = $this->rawInput('content');
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
            
            $this->setMessage('Article updated successfully.');
            return $this->redirect('/admin');
        } else {
            $this->setError('Error updating article. Please try again.');
            return $this->redirect('/admin/articles/' . $id);
        }
    }
    
    public function delete($id)
    {
        if ($this->articleModel->delete($id)) {
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
