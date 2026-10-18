<div class="container">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo $basePath; ?>/">Knowledge Base</a></li>
        <?php if (!empty($ancestors)): ?>
            <?php foreach ($ancestors as $ancestor): ?>
                <li class="breadcrumb-item"><a href="<?php echo $basePath; ?>/category/<?php echo htmlspecialchars($ancestor['slug']); ?>"><?php echo htmlspecialchars($ancestor['name']); ?></a></li>
            <?php endforeach; ?>
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

                    <?php if (!empty($attachments)): ?>
                        <div class="mt-4 pt-3 border-top">
                            <h2 class="h5 mb-3">
                                <i class="bi bi-paperclip me-2"></i>Attachments
                            </h2>
                            <ul class="list-group">
                                <?php foreach ($attachments as $attachment): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>
                                            <i class="bi bi-file-earmark me-2 text-primary"></i>
                                            <?php echo htmlspecialchars($attachment['original_name']); ?>
                                            <small class="text-muted">(<?php echo FileStorage::formatSize($attachment['size']); ?>)</small>
                                        </span>
                                        <a href="<?php echo $basePath; ?>/attachment/<?php echo (int)$attachment['id']; ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-download me-1"></i>Download
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</div>
