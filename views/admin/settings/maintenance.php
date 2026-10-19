<h2 class="h4">Maintenance</h2>
<p class="text-muted">Take the public site offline without changing articles or categories.</p>
<form action="<?php echo $basePath; ?>/admin/settings/maintenance" method="POST">
    <?php echo Csrf::field(); ?>
    <div class="mb-3">
        <label for="maintenance_mode" class="form-label">Maintenance Mode</label>
        <?php $maintenanceMode = $this->getSetting('maintenance_mode'); ?>
        <select class="form-select<?php echo ($invalidField === 'maintenance_mode') ? ' is-invalid' : ''; ?>" id="maintenance_mode" name="maintenance_mode">
            <option value="disabled" <?php echo ($maintenanceMode === 'disabled') ? 'selected' : ''; ?>>Disabled</option>
            <option value="enabled" <?php echo ($maintenanceMode === 'enabled') ? 'selected' : ''; ?>>Enabled</option>
        </select>
        <?php if ($invalidField === 'maintenance_mode' && !empty($error)): ?>
            <div class="invalid-feedback"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
    </div>
    <div class="mb-3">
        <label for="maintenance_message" class="form-label">Maintenance Message</label>
        <textarea class="form-control" id="maintenance_message" name="maintenance_message" rows="3"><?php echo htmlspecialchars($this->getSetting('maintenance_message')); ?></textarea>
    </div>
    <button type="submit" class="btn btn-dark">Save maintenance</button>
    <div class="form-text mt-2">Only Maintenance is saved. General, Appearance, Editor, and API stay as they are.</div>
</form>
