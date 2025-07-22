<div class="container">
    
    <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page">Knowledge Base</li>
    </ol>
    
    <?php if (empty($categories)): ?>
        <p><i>No categories present.</i></p>
    <?php else: ?>
        <?php foreach ($categories as $category): ?>
            <div class="category-item">
                <a href="/category/<?php echo htmlspecialchars($category['slug']); ?>">
                    <div class="category-inner">
                        <div class="category-icon">
                            <i class="bi bi-<?php echo htmlspecialchars($category['icon']); ?>"></i>
                        </div>
                        <div class="category-content">
                            <h6><?php echo htmlspecialchars($category['name']); ?> <span class="num-articles">(<?php echo $category['article_count']; ?>)</span></h6>
                            <p><?php echo htmlspecialchars($category['description']); ?></p>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
