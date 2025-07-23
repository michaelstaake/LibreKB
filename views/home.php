<div class="container">
    
    <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page">Knowledge Base</li>
    </ol>
    
    <?php if (empty($categories)): ?>
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="bi bi-folder-x display-1 text-muted"></i>
            </div>
            <h3 class="text-muted mb-3">No categories available</h3>
            <p class="text-muted mb-4">There are currently no categories to browse.</p>
            <a href="/search" class="btn btn-primary">
                <i class="bi bi-search me-2"></i>Search instead
            </a>
        </div>
    <?php else: ?>
        <header class="mb-4">
            <h1 class="mb-2">Browse Categories</h1>
            <p class="text-muted">Explore our knowledge base by category</p>
        </header>
        
        <div class="row">
            <?php foreach ($categories as $category): ?>
                <div class="col-12 col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm border-0 category-result-card">
                        <a href="/category/<?php echo htmlspecialchars($category['slug']); ?>" class="text-decoration-none">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-start">
                                    <div class="me-3">
                                        <div class="bg-dark bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                                            <?php if ($category['icon']): ?>
                                                <i class="bi bi-<?php echo htmlspecialchars($category['icon']); ?> text-white fs-4"></i>
                                            <?php else: ?>
                                                <i class="bi bi-folder text-white fs-4"></i>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="card-title mb-2 text-dark">
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </h5>
                                        <?php if ($category['description']): ?>
                                            <p class="card-text text-muted small mb-3" style="line-height: 1.4;">
                                                <?php echo htmlspecialchars($category['description']); ?>
                                            </p>
                                        <?php endif; ?>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-dark">
                                                <?php echo $category['article_count']; ?> article<?php echo $category['article_count'] !== 1 ? 's' : ''; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
