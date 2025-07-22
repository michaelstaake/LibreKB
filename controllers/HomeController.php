<?php

class HomeController extends Controller
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
    
    public function index()
    {
        // Check maintenance mode
        $maintenanceMode = $this->settingModel->getValue('maintenance_mode');
        if ($maintenanceMode === 'enabled') {
            return $this->showMaintenance();
        }
        
        // Check knowledge base access
        $this->checkKnowledgeBaseAccess();
        
        // Get site settings
        $siteName = $this->settingModel->getValue('site_name') ?: 'Knowledge Base';
        
        // Get categories
        $categories = $this->categoryModel->getEnabledTopLevel();
        
        // Add article counts to categories (including subcategories)
        foreach ($categories as &$category) {
            $category['article_count'] = $this->categoryModel->countEnabledArticlesRecursive($category['id']);
        }
        
        $data = [
            'pageTitle' => 'Categories',
            'siteName' => $siteName,
            'categories' => $categories,
            'message' => $this->getMessage(),
            'error' => $this->getError()
        ];
        
        return $this->layout('main', 'home', $data);
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
