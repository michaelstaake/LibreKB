<div class="container">
    <header>
        <h1>Settings</h1>
    </header>
    
    <?php if ($user['group'] !== 'admin'): ?>
        <div class="alert alert-danger" role="alert">You do not have permission to access this page.</div>
    <?php else: ?>
        <main>
            <form action="/admin/settings" method="POST">
                <div class="settings-section">
                    <h5>Branding and Customization</h5>
                    <div class="row">
                        <div class="col">
                            <div class="mb-3">
                                <label for="site_name" class="form-label">Site Name</label>
                                <input type="text" class="form-control" id="site_name" name="site_name" value="<?php echo htmlspecialchars($this->getSetting('site_name')); ?>">
                                <div class="form-text">If this is blank, the default name "Knowledge Base" will be used.</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="mb-3">
                                <label for="site_color" class="form-label">Site Color</label>
                                <input type="text" class="form-control" id="site_color" name="site_color" value="<?php echo htmlspecialchars($this->getSetting('site_color')); ?>">
                                <div class="form-text">If this is blank, the default color of #1B1F22 will be used.</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="mb-3">
                                <label for="site_logo" class="form-label">Site Logo</label>
                                <input type="text" class="form-control" id="site_logo" name="site_logo" value="<?php echo htmlspecialchars($this->getSetting('site_logo')); ?>">
                                <div class="form-text">If this is blank, the site title will be displayed in the header.</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="settings-section">
                    <h5>Access Control</h5>
                    <div class="row">
                        <div class="col">
                            <div class="mb-3">
                                <label for="kb_visibility" class="form-label">Knowledge Base Visibility</label>
                                <?php $kbVisibility = $this->getSetting('kb_visibility', 'public'); ?>
                                <select class="form-select" id="kb_visibility" name="kb_visibility">
                                    <option value="public" <?php echo ($kbVisibility === 'public') ? 'selected' : ''; ?>>Public (anyone can view)</option>
                                    <option value="private" <?php echo ($kbVisibility === 'private') ? 'selected' : ''; ?>>Private (logged in users only)</option>
                                </select>
                                <div class="form-text">Public allows anyone to view the knowledge base. Private requires users to be logged in.</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="settings-section">
                    <h5>Maintenance Mode</h5>
                    <div class="row">
                        <div class="col">
                            <div class="mb-3">
                                <label for="maintenance_mode" class="form-label">Maintenance Mode</label>
                                <?php $maintenanceMode = $this->getSetting('maintenance_mode'); ?>
                                <select class="form-select" id="maintenance_mode" name="maintenance_mode">
                                    <option value="disabled" <?php echo ($maintenanceMode === 'disabled') ? 'selected' : ''; ?>>Disabled</option>
                                    <option value="enabled" <?php echo ($maintenanceMode === 'enabled') ? 'selected' : ''; ?>>Enabled</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="maintenance_message" class="form-label">Maintenance Message</label>
                                <textarea class="form-control" id="maintenance_message" name="maintenance_message" rows="3"><?php echo htmlspecialchars($this->getSetting('maintenance_message')); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="settings-section">
                    <button type="submit" class="btn btn-dark">Save</button>
                </div>           
            </form>
        </main>
    <?php endif; ?>
</div>
