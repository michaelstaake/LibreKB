<div class="container">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">Knowledge Base</a></li>
        <li class="breadcrumb-item"><a href="/search">Search</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($query); ?></li>
    </ol>
    
    <header>
        <h1>Search Results for "<?php echo htmlspecialchars($query); ?>"</h1>
    </header>
    
    <main>
        <?php if (empty($results)): ?>
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="bi bi-search display-1 text-muted"></i>
                </div>
                <h3 class="text-muted mb-3">No results found</h3>
                <p class="text-muted mb-4">We couldn't find anything matching "<strong><?php echo htmlspecialchars($query); ?></strong>"</p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="/search" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-2"></i>Try another search
                    </a>
                    <a href="/" class="btn btn-primary">
                        <i class="bi bi-house me-2"></i>Browse categories
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="text-muted mb-1">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Found <?php echo count($results); ?> result<?php echo count($results) !== 1 ? 's' : ''; ?>
                    </h5>
                </div>
                <a href="/search" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>New search
                </a>
            </div>
            
            <div class="row">
                <?php foreach ($results as $result): ?>
                    <div class="col-12 mb-3">
                        <div class="card h-100 shadow-sm border-0 search-result-card">
                            <?php if ($result['type'] === 'category'): ?>
                                <a href="/category/<?php echo htmlspecialchars($result['slug']); ?>" class="text-decoration-none">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-start">
                                            <div class="me-3">
                                                <div class="bg-dark bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                                    <i class="bi bi-folder text-white fs-5"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="card-title mb-2 text-dark">
                                                    <?php echo htmlspecialchars($result['title']); ?>
                                                </h6>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-dark me-2">Category</span>
                                                    <small class="text-muted">Browse articles in this category</small>
                                                </div>
                                            </div>
                                            <div class="ms-auto">
                                                <i class="bi bi-chevron-right text-muted"></i>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            <?php else: ?>
                                <a href="/article/<?php echo htmlspecialchars($result['slug']); ?>" class="text-decoration-none">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-start">
                                            <div class="me-3">
                                                <div class="bg-primary bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                                    <i class="bi bi-file-earmark-text text-white fs-5"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="card-title mb-2 text-dark">
                                                    <?php echo htmlspecialchars($result['title']); ?>
                                                </h6>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-primary text-white me-2">Article</span>
                                                    <?php if ($result['category_name']): ?>
                                                        <small class="text-muted">
                                                            in <strong><?php echo htmlspecialchars($result['category_name']); ?></strong>
                                                        </small>
                                                    <?php else: ?>
                                                        <small class="text-muted">Knowledge base article</small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="ms-auto">
                                                <i class="bi bi-chevron-right text-muted"></i>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</div>
