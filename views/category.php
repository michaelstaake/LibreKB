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
            <?php foreach ($subCategories as $subCategory): ?>
                <div class="article-item">
                    <a href="/category/<?php echo htmlspecialchars($subCategory['slug']); ?>">
                        <div>
                            <h6><i class="bi bi-<?php echo htmlspecialchars($subCategory['icon']); ?>"></i> <?php echo htmlspecialchars($subCategory['name']); ?> <span class="num-articles">(<?php echo $subCategory['article_count']; ?>)</span></h6>
                            <p><?php echo htmlspecialchars($subCategory['description']); ?></p>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php if (empty($articles)): ?>
            <?php if (!$hasSubCategories): ?>
                <p><i>No content in this category.</i></p>
            <?php endif; ?>
        <?php else: ?>
            <?php foreach ($articles as $article): ?>
                <div class="article-item">
                    <a href="/article/<?php echo htmlspecialchars($article['slug']); ?>">
                        <div>
                            <h6><i class="bi bi-file-earmark"></i> <?php echo htmlspecialchars($article['title']); ?></h6>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</div>
