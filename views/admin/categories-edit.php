<div class="container">
    
    <main>
        <header>
            <div>
                <h1>Edit Category</h1>
            </div>
            <hr>
        </header>
        <form action="<?php echo $basePath; ?>/admin/categories/<?php echo $category['id']; ?>" method="POST">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo htmlspecialchars($category['name']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($category['description']); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="icon" class="form-label">Icon (Bootstrap Icons)</label>
                        <input type="text" class="form-control" id="icon" name="icon" 
                               value="<?php echo htmlspecialchars($category['icon']); ?>"
                               placeholder="e.g., book, question-circle, gear">
                        <small class="form-text text-muted">
                            Browse icons at <a href="https://icons.getbootstrap.com/" target="_blank">Bootstrap Icons</a>
                        </small>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="parent" class="form-label">Parent Category</label>
                        <select class="form-select" id="parent" name="parent">
                            <option value="">Top Level Category</option>
                            <?php foreach ($parentCategories as $parentCategory): ?>
                                <option value="<?php echo $parentCategory['id']; ?>"
                                        <?php echo $parentCategory['id'] == $category['parent'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($parentCategory['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="enabled" <?php echo $category['status'] === 'enabled' ? 'selected' : ''; ?>>Enabled</option>
                            <option value="disabled" <?php echo $category['status'] === 'disabled' ? 'selected' : ''; ?>>Disabled</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="order" class="form-label">Order</label>
                        <input type="number" class="form-control" id="order" name="order" 
                               value="<?php echo htmlspecialchars($category['order']); ?>" min="0">
                        <div class="form-text">Controls display order (0 = first, higher numbers = later)</div>
                    </div>
                    
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">Update Category</button>
                        <a href="<?php echo $basePath; ?>/admin" class="btn btn-secondary">Cancel</a>
                        <a href="<?php echo $basePath; ?>/category/<?php echo htmlspecialchars($category['slug']); ?>" 
                           class="btn btn-outline-info" target="_blank">View</a>
                    </div>
                </div>
            </div>
        </form>
    </main>
</div>
