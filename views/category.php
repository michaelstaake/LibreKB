<div class="container">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">Knowledge Base</a></li>
        <?php if ($parentCategory): ?>
            <li class="breadcrumb-item"><a href="/category/<?php echo htmlspecialchars($parentCategory['slug']); ?>"><?php echo htmlspecialchars($parentCategory['name']); ?></a></li>
        <?php endif; ?>
        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($category['name']); ?></li>
    </ol>
    
    <header>
        <h1><?php echo htmlspecialchars($category['name']); ?></h1>
        <p><?php echo htmlspecialchars($category['description']); ?></p>
    </header>
    
    <main>
        <?php $hasSubCategories = !empty($subCategories); ?>
        
        <?php if ($hasSubCategories): ?>
            <div class="mb-3">
                <h4 class="mb-3">
                    <i class="bi bi-folder me-2 text-primary"></i>Subcategories
                </h4>
                <div class="row">
                    <?php foreach ($subCategories as $subCategory): ?>
                        <div class="col-12 col-md-6 col-lg-4 mb-3">
                            <div class="card h-100 shadow-sm border-0 category-result-card">
                                <a href="/category/<?php echo htmlspecialchars($subCategory['slug']); ?>" class="text-decoration-none">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-start">
                                            <div class="me-3">
                                                <div class="bg-dark bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                                    <?php if ($subCategory['icon']): ?>
                                                        <i class="bi bi-<?php echo htmlspecialchars($subCategory['icon']); ?> text-white fs-5"></i>
                                                    <?php else: ?>
                                                        <i class="bi bi-folder text-white fs-5"></i>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="card-title mb-2 text-dark">
                                                    <?php echo htmlspecialchars($subCategory['name']); ?>
                                                </h6>
                                                <?php if ($subCategory['description']): ?>
                                                    <p class="card-text text-muted small mb-2" style="line-height: 1.4;">
                                                        <?php echo htmlspecialchars($subCategory['description']); ?>
                                                    </p>
                                                <?php endif; ?>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-dark">
                                                        <?php echo $subCategory['article_count']; ?> article<?php echo $subCategory['article_count'] !== 1 ? 's' : ''; ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ms-auto">
                                                <i class="bi bi-chevron-right text-muted"></i>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (empty($articles)): ?>
            <?php if (!$hasSubCategories): ?>
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-file-earmark-x display-1 text-muted"></i>
                    </div>
                    <h4 class="text-muted mb-3">No articles found</h4>
                    <p class="text-muted mb-4">This category doesn't contain any articles yet.</p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="/" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-left me-2"></i>Back to categories
                        </a>
                        <a href="/search" class="btn btn-primary">
                            <i class="bi bi-search me-2"></i>Search articles
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div>
                <?php if ($hasSubCategories): ?>
                    <h4 class="mb-3">
                        <i class="bi bi-file-earmark-text me-2 text-info"></i>Articles
                    </h4>
                <?php endif; ?>
                <div class="row">
                    <?php foreach ($articles as $article): ?>
                        <div class="col-12 mb-3">
                            <div class="card shadow-sm border-0 search-result-card">
                                <a href="/article/<?php echo htmlspecialchars($article['slug']); ?>" class="text-decoration-none">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-start">
                                            <div class="me-3">
                                                <div class="bg-primary bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                                    <i class="bi bi-file-earmark-text text-white fs-5"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="card-title mb-2 text-dark">
                                                    <?php echo htmlspecialchars($article['title']); ?>
                                                </h6>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-primary text-white">Article</span>
                                                </div>
                                            </div>
                                            <div class="ms-auto">
                                                <i class="bi bi-chevron-right text-muted"></i>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>
