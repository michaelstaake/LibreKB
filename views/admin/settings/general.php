<h2 class="h4">General</h2>
<p class="text-muted">The name of the knowledge base and who can read it.</p>
<form action="<?php echo $basePath; ?>/admin/settings/general" method="POST">
    <?php echo Csrf::field(); ?>
    <div class="mb-3">
        <label for="site_name" class="form-label">Site Name</label>
        <input type="text" class="form-control" id="site_name" name="site_name" value="<?php echo htmlspecialchars($this->getSetting('site_name')); ?>">
        <div class="form-text">If this is blank, the default name "Knowledge Base" will be used.</div>
    </div>
    <div class="mb-3">
        <label for="kb_visibility" class="form-label">Knowledge Base Visibility</label>
        <?php $kbVisibility = $this->getSetting('kb_visibility', 'public'); ?>
        <select class="form-select<?php echo ($invalidField === 'kb_visibility') ? ' is-invalid' : ''; ?>" id="kb_visibility" name="kb_visibility">
            <option value="public" <?php echo ($kbVisibility === 'public') ? 'selected' : ''; ?>>Public (anyone can view)</option>
            <option value="private" <?php echo ($kbVisibility === 'private') ? 'selected' : ''; ?>>Private (logged in users only)</option>
        </select>
        <?php if ($invalidField === 'kb_visibility' && !empty($error)): ?>
            <div class="invalid-feedback"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <div class="form-text">Public allows anyone to view the knowledge base. Private requires users to be logged in.</div>
    </div>
    <button type="submit" class="btn btn-dark">Save general</button>
    <div class="form-text mt-2">Only General is saved. Appearance, Editor, API, and Maintenance stay as they are.</div>
</form>
