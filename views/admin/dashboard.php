<div class="container">
    
    <header class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Dashboard</h1>
            <p class="text-muted mb-0">Welcome to the LibreKB Admin Panel - Manage your knowledge base content</p>
        </div>
    </header>
    
    <main>
        <div class="row mb-4">
            <?php if ($user['group'] === 'admin'): ?>
                <!-- Admin view: 3 columns -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title">
                                <i class="bi bi-folder2 text-warning"></i> Categories
                            </h5>
                            <h2><?php echo $enabledCategories; ?>/<?php echo $totalCategories; ?></h2>
                            <small class="text-muted">Enabled/Total categories in your knowledge base</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title">
                                <i class="bi bi-file-earmark-text text-info"></i> Articles
                            </h5>
                            <h2><?php echo $enabledArticles; ?>/<?php echo $totalArticles; ?></h2>
                            <small class="text-muted">Enabled/Total articles in your knowledge base</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title">
                                <i class="bi bi-gear text-success"></i> Administration
                            </h5>
                            <div class="d-grid gap-2">
                                <a href="/admin/users" class="btn btn-outline-success btn-sm">Manage Users</a>
                                <a href="/admin/settings" class="btn btn-outline-primary btn-sm">Site Settings</a>
                                <a href="/admin/logs" class="btn btn-outline-secondary btn-sm">Activity Log</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Manager view: 2 columns -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title">
                                <i class="bi bi-folder2 text-warning"></i> Categories
                            </h5>
                            <h2><?php echo $enabledCategories; ?>/<?php echo $totalCategories; ?></h2>
                            <small class="text-muted">Enabled/Total categories in your knowledge base</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title">
                                <i class="bi bi-file-earmark-text text-info"></i> Articles
                            </h5>
                            <h2><?php echo $enabledArticles; ?>/<?php echo $totalArticles; ?></h2>
                            <small class="text-muted">Enabled/Total articles in your knowledge base</small>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (empty($categories)): ?>
            <div class="alert alert-info" role="alert">
                <h5><i class="bi bi-info-circle"></i> No content found</h5>
                <p class="mb-0">Start by creating your first category, then add articles to organize your knowledge base.</p>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-diagram-3"></i> Knowledge Base Structure</h5>
                    <?php if ($user['group'] === 'admin' || $user['group'] === 'manager'): ?>
                    <a href="/admin/categories/create" class="btn btn-primary btn-sm">
                        <i class="bi bi-folder-plus"></i> Create Category
                    </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php
                    // Organize categories into hierarchical structure
                    $topLevelCategories = array_filter($categories, function($cat) {
                        return is_null($cat['parent']);
                    });
                    
                    // Sort top level categories by order
                    usort($topLevelCategories, function($a, $b) {
                        return ($a['order'] + 0) - ($b['order'] + 0);
                    });
                    
                    function displayContentStructure($category, $categories, $articles, $user, $level = 0) {
                        $indent = str_repeat('    ', $level);
                        $levelClass = $level === 0 ? 'border-start border-primary border-3' : 'border-start border-secondary';
                        $bgClass = $level === 0 ? 'bg-light' : '';
                        
                        echo '<div class="mb-3 ps-3 ' . $levelClass . ' ' . $bgClass . '">';
                        echo '<div class="d-flex justify-content-between align-items-center py-2">';
                        echo '<div class="d-flex align-items-center">';
                        
                        // Category icon and name
                        if ($category['icon']) {
                            echo '<i class="bi bi-' . htmlspecialchars($category['icon']) . ' me-2 text-primary"></i>';
                        } else {
                            echo '<i class="bi bi-folder me-2 text-warning"></i>';
                        }
                        
                        echo '<h' . ($level + 5) . ' class="mb-0 me-3">';
                        echo '<a href="/admin/categories/' . $category['id'] . '" class="text-decoration-none">';
                        echo htmlspecialchars($category['name']);
                        echo '</a>';
                        echo '</h' . ($level + 5) . '>';
                        
                        // Category status
                        if ($category['status'] !== 'enabled') {
                            echo '<span class="badge bg-danger me-2">Disabled</span>';
                        }
                        
                        // Article count
                        $articleLabel = ($category['article_count'] == 1) ? 'article' : 'articles';
                        echo '<small class="text-muted">(' . $category['article_count'] . ' ' . $articleLabel . ')</small>';
                        echo '</div>';
                        
                        // Category actions (admin and manager only)
                        if ($user['group'] === 'admin' || $user['group'] === 'manager') {
                            echo '<div class="btn-group btn-group-sm" role="group">';
                            echo '<a href="/admin/articles/create?category=' . $category['id'] . '" class="btn btn-success btn-sm" title="Create Article in ' . htmlspecialchars($category['name']) . '">';
                            echo '<i class="bi bi-file-earmark-plus"></i>';
                            echo '</a>';
                            echo '<a href="/admin/categories/' . $category['id'] . '" class="btn btn-outline-primary btn-sm" title="Edit Category">';
                            echo '<i class="bi bi-pencil"></i>';
                            echo '</a>';
                            echo '<a href="/admin/categories/' . $category['id'] . '/delete" ';
                            echo 'class="btn btn-outline-danger btn-sm" title="Delete Category" ';
                            echo 'onclick="return confirm(\'Are you sure?\')"><i class="bi bi-trash"></i></a>';
                            echo '</div>';
                        }
                        echo '</div>';
                        
                        // Display articles in this category
                        $categoryArticles = array_filter($articles, function($article) use ($category) {
                            return $article['category'] == $category['id'];
                        });
                        
                        // Sort articles by order
                        usort($categoryArticles, function($a, $b) {
                            return ($a['order'] + 0) - ($b['order'] + 0);
                        });
                        
                        if (!empty($categoryArticles)) {
                            echo '<div class="ps-4">';
                            foreach ($categoryArticles as $article) {
                                echo '<div class="d-flex justify-content-between align-items-center py-1 border-bottom">';
                                echo '<div class="d-flex align-items-center">';
                                echo '<i class="bi bi-file-earmark-text me-2 text-info"></i>';
                                echo '<a href="/admin/articles/' . $article['id'] . '" class="text-decoration-none">';
                                echo htmlspecialchars($article['title']);
                                echo '</a>';
                                if ($article['status'] !== 'enabled') {
                                    echo '<span class="badge bg-danger ms-2 badge-sm">';
                                    echo ucfirst($article['status']);
                                    echo '</span>';
                                }
                                echo '</div>';
                                // Article actions (admin and manager only)
                                if ($user['group'] === 'admin' || $user['group'] === 'manager') {
                                    echo '<div class="btn-group btn-group-sm" role="group">';
                                    echo '<a href="/admin/articles/' . $article['id'] . '" class="btn btn-outline-primary btn-sm">';
                                    echo '<i class="bi bi-pencil"></i>';
                                    echo '</a>';
                                    echo '<a href="/admin/articles/' . $article['id'] . '/delete" ';
                                    echo 'class="btn btn-outline-danger btn-sm" ';
                                    echo 'onclick="return confirm(\'Are you sure?\')"><i class="bi bi-trash"></i></a>';
                                    echo '</div>';
                                }
                                echo '</div>';
                            }
                            echo '</div>';
                        }
                        
                        // Display subcategories
                        $subcategories = array_filter($categories, function($cat) use ($category) {
                            return $cat['parent'] == $category['id'];
                        });
                        
                        // Sort subcategories by order
                        usort($subcategories, function($a, $b) {
                            return ($a['order'] + 0) - ($b['order'] + 0);
                        });
                        
                        if (!empty($subcategories)) {
                            echo '<div class="ps-3 mt-2">';
                            foreach ($subcategories as $subcategory) {
                                displayContentStructure($subcategory, $categories, $articles, $user, $level + 1);
                            }
                            echo '</div>';
                        }
                        
                        echo '</div>';
                    }
                    
                    // Display all top-level categories and their content
                    foreach ($topLevelCategories as $category) {
                        displayContentStructure($category, $categories, $articles, $user);
                    }
                    
                    // Display uncategorized articles
                    $uncategorizedArticles = array_filter($articles, function($article) {
                        return is_null($article['category']) || $article['category'] === '';
                    });
                    
                    if (!empty($uncategorizedArticles)) {
                        echo '<div class="mb-3 ps-3 border-start border-warning border-3 bg-warning bg-opacity-10">';
                        echo '<div class="d-flex justify-content-between align-items-center py-2">';
                        echo '<div class="d-flex align-items-center">';
                        echo '<i class="bi bi-exclamation-triangle me-2 text-warning"></i>';
                        echo '<h5 class="mb-0 me-3">Uncategorized Articles</h5>';
                        echo '<small class="text-muted">(' . count($uncategorizedArticles) . ' articles)</small>';
                        echo '</div>';
                        echo '</div>';
                        echo '<div class="ps-4">';
                        foreach ($uncategorizedArticles as $article) {
                            echo '<div class="d-flex justify-content-between align-items-center py-1 border-bottom">';
                            echo '<div class="d-flex align-items-center">';
                            echo '<i class="bi bi-file-earmark-text me-2 text-info"></i>';
                            echo '<a href="/admin/articles/' . $article['id'] . '" class="text-decoration-none">';
                            echo htmlspecialchars($article['title']);
                            echo '</a>';
                            echo '<span class="badge bg-' . ($article['status'] === 'enabled' ? 'success' : 'secondary') . ' ms-2 badge-sm">';
                            echo ucfirst($article['status']);
                            echo '</span>';
                            echo '</div>';
                            // Article actions (admin and manager only)
                            if ($user['group'] === 'admin' || $user['group'] === 'manager') {
                                echo '<div class="btn-group btn-group-sm" role="group">';
                                echo '<a href="/admin/articles/' . $article['id'] . '" class="btn btn-outline-primary btn-sm" title="Edit Article">';
                                echo '<i class="bi bi-pencil"></i>';
                                echo '</a>';
                                echo '<a href="/admin/articles/' . $article['id'] . '/delete" ';
                                echo 'class="btn btn-outline-danger btn-sm" title="Delete Article" ';
                                echo 'onclick="return confirm(\'Are you sure?\')"><i class="bi bi-trash"></i></a>';
                                echo '</div>';
                            }
                            echo '</div>';
                        }
                        echo '</div>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>
