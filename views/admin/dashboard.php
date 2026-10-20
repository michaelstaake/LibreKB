<div class="container">
    
    <header class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Dashboard</h1>
            <p class="text-muted mb-0">Welcome to the LibreKB Admin Panel - Manage your knowledge base content</p>
        </div>
    </header>
    
    <main>
        <div class="row">
            <?php if ($user['group'] === 'admin'): ?>
                <!-- Admin view: 3 columns -->
                <div class="col-md-4 mb-4">
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
                <div class="col-md-4 mb-4">
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
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title">
                                <i class="bi bi-gear text-success"></i> Administration
                            </h5>
                            <div class="d-grid gap-2">
                                <a href="<?php echo $basePath; ?>/admin/users" class="btn btn-outline-primary btn-sm">Manage Users</a>
                                <a href="<?php echo $basePath; ?>/admin/settings" class="btn btn-outline-primary btn-sm">Site Settings</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Manager view: 2 columns -->
                <div class="col-md-6 mb-4">
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
                <div class="col-md-6 mb-4">
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
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5><i class="bi bi-info-circle"></i> No content found</h5>
                        <p class="mb-0">Start by creating your first category, then add articles to organize your knowledge base.</p>
                    </div>
                    <?php if ($user['group'] === 'admin' || $user['group'] === 'manager'): ?>
                    <a href="<?php echo $basePath; ?>/admin/categories/create" class="btn btn-primary">
                        <i class="bi bi-folder-plus"></i> Create Category
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-header kb-structure-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0"><i class="bi bi-diagram-3"></i> Knowledge Base Structure</h5>
                    <?php if ($user['group'] === 'admin' || $user['group'] === 'manager'): ?>
                    <div class="kb-structure-tools d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="expand-all">Expand all</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="collapse-all">Collapse all</button>
                        <a href="<?php echo $basePath; ?>/admin/categories/create" class="btn btn-primary btn-sm">
                            <i class="bi bi-folder-plus"></i> Create Category
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <script>document.documentElement.classList.add('js');</script>
                    <div class="mb-3">
                        <label class="visually-hidden" for="kb-filter">Filter categories and articles</label>
                        <div class="input-group kb-filter">
                            <span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
                            <input type="search" class="form-control" id="kb-filter" placeholder="Filter categories and articles" aria-label="Filter categories and articles" autocomplete="off">
                            <button type="button" class="btn btn-outline-secondary" id="kb-filter-clear" hidden>Clear</button>
                        </div>
                        <div id="kb-filter-status" class="small text-muted mt-1" role="status" aria-live="polite" hidden></div>
                    </div>
                    <div id="kb-filter-empty" class="alert alert-warning mb-0" role="status" hidden>No categories or articles match that filter.</div>
                    <div class="kb-tree-root">
                    <?php
                    $baseUrl = isset($basePath) ? $basePath : '';

                    // Organize categories into hierarchical structure
                    $topLevelCategories = array_filter($categories, function($cat) {
                        return is_null($cat['parent']);
                    });
                    
                    // Same order number keeps the query sequence: name or title, then id.
                    function compareKbOrder($a, $b) {
                        $left = $a['order'] + 0;
                        $right = $b['order'] + 0;
                        if ($left == $right) {
                            return 0;
                        }
                        return ($left < $right) ? -1 : 1;
                    }

                    usort($topLevelCategories, function($a, $b) {
                        return compareKbOrder($a, $b);
                    });
                    
                    // Helper function to check if parent category is disabled
                    function isParentDisabled($category, $categories) {
                        if (!$category['parent']) {
                            return false; // No parent, so not affected
                        }
                        
                        $parent = array_filter($categories, function($cat) use ($category) {
                            return $cat['id'] == $category['parent'];
                        });
                        
                        if (empty($parent)) {
                            return false; // Parent not found
                        }
                        
                        $parent = reset($parent);
                        if ($parent['status'] !== 'enabled') {
                            return true; // Direct parent is disabled
                        }
                        
                        // Check if any ancestor is disabled
                        return isParentDisabled($parent, $categories);
                    }
                    
                    function renderDashboardArticleRow($article, $categories, $user, $baseUrl, $uncategorized = false) {
                        echo '<div class="kb-article d-flex justify-content-between align-items-center py-1 border-bottom" data-title="' . htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') . '">';
                        echo '<div class="kb-article-main d-flex align-items-center min-w-0">';
                        echo '<i class="bi bi-file-earmark-text me-2 text-info"></i>';
                        echo '<a href="' . $baseUrl . '/admin/articles/' . $article['id'] . '" class="text-decoration-none kb-article-title" data-label="' . htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') . '">';
                        echo htmlspecialchars($article['title']);
                        echo '</a>';

                        if (!$uncategorized) {
                            $articleCategory = array_filter($categories, function($cat) use ($article) {
                                return $cat['id'] == $article['category'];
                            });
                            if (!empty($articleCategory)) {
                                $articleCategory = reset($articleCategory);
                                if ($articleCategory['status'] !== 'enabled' || isParentDisabled($articleCategory, $categories)) {
                                    echo '<i class="bi bi-exclamation-triangle text-warning ms-2" title="Category or parent category is disabled - this article is not accessible to public users"></i>';
                                }
                            }
                            if ($article['status'] !== 'enabled') {
                                echo '<span class="badge bg-danger ms-2 badge-sm">';
                                echo ucfirst(htmlspecialchars($article['status']));
                                echo '</span>';
                            }
                        } else {
                            echo '<span class="badge bg-' . ($article['status'] === 'enabled' ? 'success' : 'secondary') . ' ms-2 badge-sm">';
                            echo ucfirst(htmlspecialchars($article['status']));
                            echo '</span>';
                        }
                        echo '</div>';

                        if ($user['group'] === 'admin' || $user['group'] === 'manager') {
                            echo '<div class="btn-group btn-group-sm kb-actions" role="group">';
                            $editTitle = $uncategorized ? ' title="Edit Article"' : '';
                            echo '<a href="' . $baseUrl . '/admin/articles/' . $article['id'] . '" class="btn btn-outline-primary btn-sm"' . $editTitle . '>';
                            echo '<i class="bi bi-pencil"></i>';
                            echo '</a>';
                            echo '<form method="POST" action="' . $baseUrl . '/admin/articles/' . (int)$article['id'] . '/delete" class="d-inline" ';
                            echo 'onsubmit="return confirm(\'Are you sure?\')">';
                            echo Csrf::field();
                            echo '<button type="submit" class="btn btn-outline-danger btn-sm" title="Delete Article"><i class="bi bi-trash"></i></button>';
                            echo '</form>';
                            echo '</div>';
                        }
                        echo '</div>';
                    }

                    function displayContentStructure($category, $categories, $articles, $user, $baseUrl, $level = 0) {
                        $levelClass = $level === 0 ? 'border-start border-primary border-3' : 'border-start border-secondary';
                        $bgClass = $level === 0 ? 'bg-light' : '';
                        $categoryId = (int)$category['id'];
                        $childrenId = 'kb-children-' . $categoryId;

                        $categoryArticles = array_values(array_filter($articles, function($article) use ($category) {
                            return $article['category'] == $category['id'];
                        }));
                        usort($categoryArticles, function($a, $b) {
                            return compareKbOrder($a, $b);
                        });

                        $subcategories = array_values(array_filter($categories, function($cat) use ($category) {
                            return $cat['parent'] == $category['id'];
                        }));
                        usort($subcategories, function($a, $b) {
                            return compareKbOrder($a, $b);
                        });

                        $hasChildren = !empty($categoryArticles) || !empty($subcategories);
                        $articleCount = (int)$category['article_count'];
                        $countText = '(' . $articleCount . ' ' . ($articleCount === 1 ? 'article' : 'articles');
                        $subCount = count($subcategories);
                        if ($subCount > 0) {
                            $countText .= ' · ' . $subCount . ' ' . ($subCount === 1 ? 'subcategory' : 'subcategories');
                        }
                        $countText .= ')';

                        echo '<div class="kb-node mb-3 ps-3 ' . $levelClass . ' ' . $bgClass . ($hasChildren ? ' is-collapsed' : '') . '" id="kb-node-' . $categoryId . '" data-id="' . $categoryId . '" data-name="' . htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') . '">';
                        echo '<div class="kb-row d-flex justify-content-between align-items-center py-2">';
                        echo '<div class="kb-row-main d-flex align-items-center min-w-0">';

                        if ($hasChildren) {
                            echo '<button type="button" class="kb-toggle" id="kb-toggle-' . $categoryId . '" aria-expanded="false" aria-controls="' . $childrenId . '" aria-label="Expand ' . htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') . '">';
                            echo '<i class="bi bi-chevron-right" aria-hidden="true"></i>';
                            echo '</button>';
                        } else {
                            echo '<span class="kb-spacer" aria-hidden="true"></span>';
                        }

                        if ($category['icon']) {
                            echo '<i class="bi bi-' . htmlspecialchars($category['icon']) . ' me-2 text-primary"></i>';
                        } else {
                            echo '<i class="bi bi-folder me-2 text-warning"></i>';
                        }

                        $headingLevel = min(6, $level + 5);
                        echo '<h' . $headingLevel . ' class="kb-name mb-0 me-3">';
                        echo '<a href="' . $baseUrl . '/admin/categories/' . $categoryId . '" class="text-decoration-none kb-label" data-label="' . htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') . '">';
                        echo htmlspecialchars($category['name']);
                        echo '</a>';
                        echo '</h' . $headingLevel . '>';

                        if (isParentDisabled($category, $categories)) {
                            echo '<i class="bi bi-exclamation-triangle text-warning me-2" title="Parent category is disabled - this content is not accessible to public users"></i>';
                        }

                        if ($category['status'] !== 'enabled') {
                            echo '<span class="badge bg-danger me-2">Disabled</span>';
                        }

                        echo '<small class="kb-count text-muted text-nowrap">' . htmlspecialchars($countText) . '</small>';
                        echo '</div>';

                        if ($user['group'] === 'admin' || $user['group'] === 'manager') {
                            echo '<div class="btn-group btn-group-sm kb-actions" role="group">';
                            echo '<a href="' . $baseUrl . '/admin/articles/create?category=' . $categoryId . '" class="btn btn-success btn-sm" title="Create Article in ' . htmlspecialchars($category['name']) . '">';
                            echo '<i class="bi bi-file-earmark-plus"></i>';
                            echo '</a>';
                            echo '<a href="' . $baseUrl . '/admin/categories/' . $categoryId . '" class="btn btn-outline-primary btn-sm" title="Edit Category">';
                            echo '<i class="bi bi-pencil"></i>';
                            echo '</a>';
                            echo '<form method="POST" action="' . $baseUrl . '/admin/categories/' . $categoryId . '/delete" class="d-inline" ';
                            echo 'onsubmit="return confirm(\'Delete this category? It can only be deleted when it has no articles and no subcategories.\')">';
                            echo Csrf::field();
                            echo '<button type="submit" class="btn btn-outline-danger btn-sm" title="Delete Category"><i class="bi bi-trash"></i></button>';
                            echo '</form>';
                            echo '</div>';
                        }
                        echo '</div>';

                        if ($hasChildren) {
                            echo '<div class="kb-children" id="' . $childrenId . '">';
                            if (!empty($categoryArticles)) {
                                echo '<div class="ps-4 kb-articles">';
                                foreach ($categoryArticles as $article) {
                                    renderDashboardArticleRow($article, $categories, $user, $baseUrl, false);
                                }
                                echo '</div>';
                            }
                            if (!empty($subcategories)) {
                                echo '<div class="ps-3 mt-2 kb-subs">';
                                foreach ($subcategories as $subcategory) {
                                    displayContentStructure($subcategory, $categories, $articles, $user, $baseUrl, $level + 1);
                                }
                                echo '</div>';
                            }
                            echo '</div>';
                        }

                        echo '</div>';
                    }

                    foreach ($topLevelCategories as $category) {
                        displayContentStructure($category, $categories, $articles, $user, $baseUrl);
                    }

                    $uncategorizedArticles = array_values(array_filter($articles, function($article) {
                        return is_null($article['category']) || $article['category'] === '';
                    }));
                    usort($uncategorizedArticles, function($a, $b) {
                        return compareKbOrder($a, $b);
                    });

                    if (!empty($uncategorizedArticles)) {
                        $uncategorizedCount = count($uncategorizedArticles);
                        echo '<div class="kb-node mb-3 ps-3 border-start border-warning border-3 bg-warning bg-opacity-10 is-collapsed" id="kb-node-uncategorized" data-id="uncategorized" data-name="Uncategorized Articles">';
                        echo '<div class="kb-row d-flex justify-content-between align-items-center py-2">';
                        echo '<div class="kb-row-main d-flex align-items-center min-w-0">';
                        echo '<button type="button" class="kb-toggle" id="kb-toggle-uncategorized" aria-expanded="false" aria-controls="kb-children-uncategorized" aria-label="Expand Uncategorized Articles">';
                        echo '<i class="bi bi-chevron-right" aria-hidden="true"></i>';
                        echo '</button>';
                        echo '<i class="bi bi-exclamation-triangle me-2 text-warning"></i>';
                        echo '<h5 class="kb-name mb-0 me-3"><span class="kb-label" data-label="Uncategorized Articles">Uncategorized Articles</span></h5>';
                        echo '<small class="kb-count text-muted text-nowrap">(' . $uncategorizedCount . ' articles)</small>';
                        echo '</div>';
                        echo '</div>';
                        echo '<div class="kb-children" id="kb-children-uncategorized">';
                        echo '<div class="ps-4 kb-articles">';
                        foreach ($uncategorizedArticles as $article) {
                            renderDashboardArticleRow($article, $categories, $user, $baseUrl, true);
                        }
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                    }
                    ?>
                    </div>
                    <script src="<?php echo $basePath; ?>/js/admin-dashboard.js"></script>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>
