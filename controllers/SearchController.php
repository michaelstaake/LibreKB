<?php

class SearchController extends Controller
{
    private $settingModel;
    private $searchModel;
    
    public function __construct()
    {
        $this->settingModel = new Setting();
        $this->searchModel = new Search();
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
        
        $siteName = $this->settingModel->getValue('site_name') ?: 'Knowledge Base';
        
        $data = [
            'pageTitle' => 'Search',
            'siteName' => $siteName,
            'message' => $this->getMessage(),
            'error' => $this->getError()
        ];
        
        return $this->layout('main', 'search', $data);
    }
    
    public function search()
    {
        // Check maintenance mode
        $maintenanceMode = $this->settingModel->getValue('maintenance_mode');
        if ($maintenanceMode === 'enabled') {
            return $this->showMaintenance();
        }
        
        // Check knowledge base access
        $this->checkKnowledgeBaseAccess();
        
        $query = $this->input('query');
        if (empty($query)) {
            return $this->redirect('/search');
        }
        
        $siteName = $this->settingModel->getValue('site_name') ?: 'Knowledge Base';
        $results = $this->searchModel->search($query);
        $results = $this->addCategoryPaths($results);
        
        $data = [
            'pageTitle' => 'Search Results',
            'siteName' => $siteName,
            'query' => $query,
            'results' => $results,
            'message' => $this->getMessage(),
            'error' => $this->getError()
        ];
        
        return $this->layout('main', 'search-results', $data);
    }
    
    private function addCategoryPaths($results)
    {
        $categoryModel = new Category();

        foreach ($results as &$result) {
            if ($result['type'] === 'article' && !empty($result['category_id'])) {
                $result['category_name'] = $this->categoryPathLabel($categoryModel, $result['category_id'], true);
            } elseif ($result['type'] === 'category') {
                $parentPath = $this->categoryPathLabel($categoryModel, $result['id'], false);
                if ($parentPath !== '') {
                    $result['category_name'] = $parentPath;
                }
            }
        }
        unset($result);

        return $results;
    }

    private function categoryPathLabel($categoryModel, $categoryId, $includeSelf)
    {
        $names = [];
        foreach ($categoryModel->getAncestors($categoryId) as $ancestor) {
            $names[] = $ancestor['name'];
        }

        if ($includeSelf) {
            $category = $categoryModel->find($categoryId);
            if ($category) {
                $names[] = $category['name'];
            }
        }

        return implode(' / ', $names);
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
