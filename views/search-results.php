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
            <p>No results found for your search query.</p>
        <?php else: ?>
            <?php foreach ($results as $result): ?>
                <div class="article-item">
                    <?php if ($result['type'] === 'category'): ?>
                        <a href="/category/<?php echo htmlspecialchars($result['slug']); ?>">
                            <div>
                                <h6><i class="bi bi-folder"></i> <?php echo htmlspecialchars($result['title']); ?></h6>
                                <small class="text-muted">Category</small>
                            </div>
                        </a>
                    <?php else: ?>
                        <a href="/article/<?php echo htmlspecialchars($result['slug']); ?>">
                            <div>
                                <h6><i class="bi bi-file-earmark"></i> <?php echo htmlspecialchars($result['title']); ?></h6>
                                <?php if ($result['category_name']): ?>
                                    <small class="text-muted">in <strong><?php echo htmlspecialchars($result['category_name']); ?></strong></small>
                                <?php else: ?>
                                    <small class="text-muted">Article</small>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</div>
