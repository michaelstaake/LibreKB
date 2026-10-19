<div class="container">
    
    <main>
        <header>
            <div>
                <h1>Create Article</h1>
            </div>
            <hr>
        </header>
        <?php if (empty($categories)): ?>
            <div class="alert alert-warning">
                <h4 class="alert-heading">No Categories Found</h4>
                <p>You need to create a category before you can create an article.</p>
                <hr>
                <a href="<?php echo $basePath; ?>/admin/categories/create" class="btn btn-primary">Create Category</a>
            </div>
        <?php else: ?>
            <form action="<?php echo $basePath; ?>/admin/articles/create" method="POST" enctype="multipart/form-data">
                <?php echo Csrf::field(); ?>
                <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content" class="form-label">Content</label>
                        <textarea class="form-control" id="content" name="content" rows="15" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="attachments" class="form-label">Attachments</label>
                        <input type="file" class="form-control" id="attachments" name="attachments[]" multiple>
                        <div class="form-text">
                            Optional. Up to <?php echo (int)FileStorage::MAX_FILES; ?> files, <?php echo FileStorage::formatSize(FileStorage::MAX_BYTES); ?> each.
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
                                $selectedId = $selectedCategoryId ?? null;
                                include VIEWS_PATH . '/admin/partials/category-options.php';
                            ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="enabled">Enabled</option>
                            <option value="disabled">Disabled</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="order" class="form-label">Order</label>
                        <input type="number" class="form-control" id="order" name="order" value="1" min="0">
                        <div class="form-text">Leave empty to auto-assign the next available order</div>
                    </div>
                    
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">Create Article</button>
                        <a href="<?php echo $basePath; ?>/admin" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
        <?php endif; ?>
    </main>
</div>

<?php if (!empty($categories)): ?>
    <?php $syncEditorOnSubmit = true; ?>
    <?php include VIEWS_PATH . '/admin/partials/article-editor-script.php'; ?>
<?php endif; ?>
