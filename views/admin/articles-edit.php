<div class="container">
    <main>
         <header>
            <div>
                <h1>Edit Article</h1>
            </div>
            <hr>
        </header>
        <form action="<?php echo $basePath; ?>/admin/articles/<?php echo $article['id']; ?>" method="POST" enctype="multipart/form-data">
            <?php echo Csrf::field(); ?>
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?php echo htmlspecialchars($article['title']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content" class="form-label">Content</label>
                        <textarea class="form-control" id="content" name="content" rows="15" required><?php echo htmlspecialchars(HtmlSanitizer::clean($article['content'])); ?></textarea>
                    </div>

                    <?php if (!empty($attachments)): ?>
                        <div class="mb-3">
                            <label class="form-label">Current attachments</label>
                            <ul class="list-group">
                                <?php foreach ($attachments as $attachment): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>
                                            <i class="bi bi-paperclip me-2"></i>
                                            <?php echo htmlspecialchars($attachment['original_name']); ?>
                                            <small class="text-muted">(<?php echo FileStorage::formatSize($attachment['size']); ?>)</small>
                                        </span>
                                        <span class="d-flex gap-2">
                                            <a href="<?php echo $basePath; ?>/attachment/<?php echo (int)$attachment['id']; ?>" class="btn btn-outline-secondary btn-sm">Download</a>
                                            <button type="submit" class="btn btn-outline-danger btn-sm" form="attachment-delete-<?php echo (int)$attachment['id']; ?>">Remove</button>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="attachments" class="form-label">Add attachments</label>
                        <input type="file" class="form-control" id="attachments" name="attachments[]" multiple>
                        <div class="form-text">
                            Up to <?php echo (int)FileStorage::MAX_FILES; ?> files, <?php echo FileStorage::formatSize(FileStorage::MAX_BYTES); ?> each.
                            Allowed: <?php echo htmlspecialchars(implode(', ', FileStorage::allowedExtensions())); ?>.
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="">Select Category</option>
                            <?php
                                $selectedId = $article['category'];
                                include VIEWS_PATH . '/admin/partials/category-options.php';
                            ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="enabled" <?php echo $article['status'] === 'enabled' ? 'selected' : ''; ?>>Enabled</option>
                            <option value="disabled" <?php echo $article['status'] === 'disabled' ? 'selected' : ''; ?>>Disabled</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="order" class="form-label">Order</label>
                        <input type="number" class="form-control" id="order" name="order" 
                               value="<?php echo htmlspecialchars($article['order']); ?>" min="0">
                        <div class="form-text">Controls display order within category (0 = first, higher numbers = later)</div>
                    </div>
                    
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">Update Article</button>
                        <a href="<?php echo $basePath; ?>/admin" class="btn btn-secondary">Cancel</a>
                        <a href="<?php echo $basePath; ?>/article/<?php echo htmlspecialchars($article['slug']); ?>" 
                           class="btn btn-outline-info" target="_blank">View</a>
                    </div>
                </div>
            </div>
        </form>
        <?php if (!empty($attachments)): ?>
            <?php foreach ($attachments as $attachment): ?>
                <form method="POST" id="attachment-delete-<?php echo (int)$attachment['id']; ?>" action="<?php echo $basePath; ?>/admin/articles/<?php echo (int)$article['id']; ?>/attachments/<?php echo (int)$attachment['id']; ?>/delete" onsubmit="return confirm('Remove this attachment?')">
                    <?php echo Csrf::field(); ?>
                </form>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</div>

<?php include VIEWS_PATH . '/admin/partials/article-editor-script.php'; ?>
