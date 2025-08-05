<?php

class CategoryController extends Controller
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
        
        // Get category
        $category = $this->categoryModel->getEnabledBySlug($slug);
        if (!$category) {
            return $this->show404();
        }
        
        // Check if category hierarchy is enabled (includes parent check)
        if (!$this->categoryModel->isCategoryHierarchyEnabled($category['id'])) {
            return $this->show404();
        }
        
        // Get site settings
        $siteName = $this->settingModel->getValue('site_name') ?: 'Knowledge Base';
        
        // Get subcategories
        $subCategories = $this->categoryModel->getEnabledWithParent($category['id']);
        
        // Add article counts to subcategories (including their subcategories)
        foreach ($subCategories as &$subCategory) {
            $subCategory['article_count'] = $this->categoryModel->countEnabledArticlesRecursive($subCategory['id']);
        }
        
        // Get articles
        $articles = $this->articleModel->getEnabledByCategoryId($category['id']);
        
        // Get parent category if exists
        $parentCategory = null;
        if ($category['parent']) {
            $parentCategory = $this->categoryModel->find($category['parent']);
        }
        
        $data = [
            'pageTitle' => $category['name'],
            'siteName' => $siteName,
            'category' => $category,
            'parentCategory' => $parentCategory,
            'subCategories' => $subCategories,
            'articles' => $articles,
            'message' => $this->getMessage(),
            'error' => $this->getError()
        ];
        
        return $this->layout('main', 'category', $data);
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
        $categories = $this->categoryModel->getAllWithArticleCount();
        
        // Update article counts to include subcategories recursively
        foreach ($categories as &$category) {
            $category['article_count'] = $this->categoryModel->countEnabledArticlesRecursive($category['id']);
        }
        
        $data = [
            'pageTitle' => 'Categories',
            'pageCategory' => 'Categories',
            'categories' => $categories,
            'message' => $this->getMessage(),
            'error' => $this->getError()
        ];
        
        return $this->layout('admin', 'admin/categories', $data);
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
        
        $parentCategories = $this->categoryModel->getParentOptions();
        
        $data = [
            'pageTitle' => 'Create Category',
            'pageCategory' => 'Categories',
            'parentCategories' => $parentCategories,
            'message' => $this->getMessage(),
            'error' => $this->getError()
        ];
        
        return $this->layout('admin', 'admin/categories-create', $data);
    }
    
    public function store()
    {
        $name = $this->rawInput('name');
        $slug = $this->generateSlug($name);
        $description = $this->rawInput('description');
        $icon = $this->input('icon');
        $parent = $this->input('parent') ?: null;
        $status = $this->input('status');
        $order = $this->input('order', 1);
        
        $categoryData = [
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'icon' => $icon,
            'parent' => $parent,
            'status' => $status,
            'order' => $order
        ];
        
        if ($this->categoryModel->create($categoryData)) {
            // Log category creation
            $user = $this->getUser();
            Log::logAction($user['id'], $user['email'], 'created', 'category');
            
            $this->setMessage('Category created successfully.');
            return $this->redirect('/admin');
        } else {
            $this->setError('Error creating category. Please try again.');
            return $this->redirect('/admin/categories/create');
        }
    }
    
    public function edit($id)
    {
        $category = $this->categoryModel->find($id);
        
        if (!$category) {
            $this->setError('Category not found.');
            return $this->redirect('/admin');
        }
        
        if ($this->isPost()) {
            return $this->update($id);
        }
        
        $parentCategories = $this->categoryModel->getParentOptions($id);
        
        $data = [
            'pageTitle' => 'Edit Category',
            'pageCategory' => 'Categories',
            'category' => $category,
            'parentCategories' => $parentCategories,
            'message' => $this->getMessage(),
            'error' => $this->getError()
        ];
        
        return $this->layout('admin', 'admin/categories-edit', $data);
    }
    
    public function update($id)
    {
        $name = $this->rawInput('name');
        $slug = $this->generateSlug($name, $id);
        $description = $this->rawInput('description');
        $icon = $this->input('icon');
        $parent = $this->input('parent') ?: null;
        $status = $this->input('status');
        $order = $this->input('order');
        
        $updateData = [
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'icon' => $icon,
            'parent' => $parent,
            'status' => $status,
            'order' => $order
        ];
        
        if ($this->categoryModel->update($id, $updateData)) {
            // Log category update
            $user = $this->getUser();
            Log::logAction($user['id'], $user['email'], 'updated', 'category');
            
            $this->setMessage('Category updated successfully.');
            return $this->redirect('/admin');
        } else {
            $this->setError('Error updating category. Please try again.');
            return $this->redirect('/admin/categories/' . $id);
        }
    }
    
    public function delete($id)
    {
        // Check if category has articles
        $articleCount = $this->articleModel->countByCategoryId($id);
        if ($articleCount > 0) {
            $this->setError('Cannot delete category that contains articles.');
            return $this->redirect('/admin');
        }
        
        // Check if category has subcategories
        $subcategoryCount = $this->categoryModel->countWithParent($id);
        if ($subcategoryCount > 0) {
            $this->setError('Cannot delete category that contains subcategories.');
            return $this->redirect('/admin');
        }
        
        if ($this->categoryModel->delete($id)) {
            // Log category deletion
            $user = $this->getUser();
            Log::logAction($user['id'], $user['email'], 'deleted', 'category');
            
            $this->setMessage('Category deleted successfully.');
            return $this->redirect('/admin');
        } else {
            $this->setError('Error deleting category. Please try again.');
            return $this->redirect('/admin');
        }
    }
    
    private function generateSlug($name, $excludeId = null)
    {
        // First, convert HTML entities back to characters
        $name = html_entity_decode($name, ENT_QUOTES, 'UTF-8');
        
        // Remove special characters and replace with spaces, then with hyphens
        $slug = preg_replace('/[^A-Za-z0-9\s-]/', '', $name);
        $slug = preg_replace('/\s+/', '-', $slug);
        $slug = strtolower(trim($slug, '-'));
        
        // Remove multiple consecutive hyphens
        $slug = preg_replace('/-+/', '-', $slug);
        
        // Check if slug exists
        $counter = 1;
        $originalSlug = $slug;
        $existingCategory = $this->categoryModel->getBySlug($slug);
        
        while ($existingCategory && (!$excludeId || $existingCategory['id'] != $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
            $existingCategory = $this->categoryModel->getBySlug($slug);
        }
        
        return $slug;
    }
}
