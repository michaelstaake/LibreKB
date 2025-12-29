<div class="container">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo $basePath; ?>/">Knowledge Base</a></li>
        <?php if ($parentCategory): ?>
            <li class="breadcrumb-item"><a href="<?php echo $basePath; ?>/category/<?php echo htmlspecialchars($parentCategory['slug']); ?>"><?php echo htmlspecialchars($parentCategory['name']); ?></a></li>
        <?php endif; ?>
        <li class="breadcrumb-item"><a href="<?php echo $basePath; ?>/category/<?php echo htmlspecialchars($category['slug']); ?>"><?php echo htmlspecialchars($category['name']); ?></a></li>
        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($article['title']); ?></li>
    </ol>
    
    <div class="row">
        <div class="col-lg-12 mx-auto">
            <div class="card shadow-sm border-0 article-card">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h1 class="mb-2"><?php echo htmlspecialchars($article['title']); ?></h1>
                </div>
                
                <div class="card-body pt-3">
                    <div class="article-content">
                        <?php echo $article['content']; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
