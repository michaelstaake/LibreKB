<div class="container">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">Knowledge Base</a></li>
        <?php if ($parentCategory): ?>
            <li class="breadcrumb-item"><a href="/category/<?php echo htmlspecialchars($parentCategory['slug']); ?>"><?php echo htmlspecialchars($parentCategory['name']); ?></a></li>
        <?php endif; ?>
        <li class="breadcrumb-item"><a href="/category/<?php echo htmlspecialchars($category['slug']); ?>"><?php echo htmlspecialchars($category['name']); ?></a></li>
        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($article['title']); ?></li>
    </ol>
    
    <header>
        <h1><?php echo htmlspecialchars($article['title']); ?></h1>
    </header>
    
    <main>
        <?php echo $article['content']; ?>
    </main>
</div>
