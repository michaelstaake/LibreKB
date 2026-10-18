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
        
        // Add article and child counts to subcategories (including deeper levels)
        foreach ($subCategories as &$subCategory) {
            $subCategory['article_count'] = $this->categoryModel->countEnabledArticlesRecursive($subCategory['id']);
            $subCategory['subcategory_count'] = count($this->categoryModel->getEnabledWithParent($subCategory['id']));
        }
        unset($subCategory);
        
        // Get articles
        $articles = $this->articleModel->getEnabledByCategoryId($category['id']);

        $data = [
            'pageTitle' => $category['name'],
            'siteName' => $siteName,
            'category' => $category,
            'ancestors' => $this->categoryModel->getAncestors($category['id']),
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
        $status = $this->input('status');
        $order = $this->input('order', 1);

        list($parent, $parentError) = $this->normalizeParent($this->rawInput('parent'));
        if ($parentError) {
            $this->setError($parentError);
            return $this->redirect('/admin/categories/create');
        }
        
        $categoryData = [
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'icon' => $icon,
            'parent' => $parent,
            'status' => $status,
            'order' => $order,
            'created' => date('Y-m-d H:i:s'),
            'updated' => date('Y-m-d H:i:s')
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
        $status = $this->input('status');
        $order = $this->input('order');

        list($parent, $parentError) = $this->normalizeParent($this->rawInput('parent'), $id);
        if ($parentError) {
            $this->setError($parentError);
            return $this->redirect('/admin/categories/' . $id);
        }
        
        $updateData = [
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'icon' => $icon,
            'parent' => $parent,
            'status' => $status,
            'order' => $order,
            'updated' => date('Y-m-d H:i:s')
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
        // Keep child categories and articles in place. The admin moves or
        // deletes that content first so a parent delete cannot orphan it.
        $articleCount = $this->articleModel->countByCategoryId($id);
        if ($articleCount > 0) {
            $this->setError('Cannot delete a category that still contains articles. Move or delete those articles first.');
            return $this->redirect('/admin');
        }
        
        $subcategoryCount = $this->categoryModel->countWithParent($id);
        if ($subcategoryCount > 0) {
            $this->setError('Cannot delete a category that still contains subcategories. Move or delete those subcategories first.');
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
    
    /**
     * Empty parent means top level. Otherwise the parent must exist and must
     * not introduce a cycle.
     */
    private function normalizeParent($parent, $categoryId = null)
    {
        if (is_array($parent)) {
            return [null, 'Please choose a valid parent category.'];
        }

        if ($parent === null || $parent === '') {
            return [null, null];
        }

        if (!ctype_digit((string)$parent)) {
            return [null, 'Please choose a valid parent category.'];
        }

        $parentId = (int)$parent;
        if (!$this->categoryModel->canBeParent($categoryId ?: 0, $parentId)) {
            if ($categoryId && (int)$categoryId === $parentId) {
                return [null, 'A category cannot be its own parent.'];
            }
            if ($categoryId && $this->categoryModel->find($parentId)) {
                return [null, 'A category cannot be placed inside itself or one of its subcategories.'];
            }
            return [null, 'The selected parent category does not exist.'];
        }

        return [$parentId, null];
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
