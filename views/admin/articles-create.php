<div class="container">
    
    <main>
        <header>
            <div>
                <h1>Create Article</h1>
            </div>
            <hr>
        </header>
        <form action="/admin/articles/create" method="POST">
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
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <?php if (empty($category['parent'])): // Top-level categories ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo (isset($selectedCategoryId) && $selectedCategoryId == $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                    <?php foreach ($categories as $subCategory): ?>
                                        <?php if ($subCategory['parent'] == $category['id']): ?>
                                            <option value="<?php echo $subCategory['id']; ?>" <?php echo (isset($selectedCategoryId) && $selectedCategoryId == $subCategory['id']) ? 'selected' : ''; ?>>
                                                &nbsp;&nbsp;&nbsp;&nbsp;└ <?php echo htmlspecialchars($subCategory['name']); ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
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
                        <a href="/admin" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </main>
</div>

<script src="/vendor/tinymce/tinymce.min.js"></script>
<script>
tinymce.init({
    selector: '#content',
    plugins: 'lists link code table emoticons searchreplace wordcount', 
    toolbar: 'undo redo | bold italic underline | blocks | fontsize | bullist numlist | link table | code | searchreplace | wordcount',
    height: 400,
    menubar: false,
    branding: false,
    content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 14px; }',
    block_formats: 'Paragraph=p; Header 1=h1; Header 2=h2; Header 3=h3; Header 4=h4; Header 5=h5; Header 6=h6; Preformatted=pre',
    fontsize_formats: '8pt 9pt 10pt 11pt 12pt 14pt 16pt 18pt 20pt 22pt 24pt 26pt 28pt 36pt 48pt 72pt',
    setup: function(editor) {
        // Ensure content is synced when form is submitted
        editor.on('change', function() {
            editor.save();
        });
    }
});

// Ensure TinyMCE content is saved to textarea before form submission
document.querySelector('form').addEventListener('submit', function(e) {
    // Trigger TinyMCE to save its content to the original textarea
    tinymce.triggerSave();
});
</script>
