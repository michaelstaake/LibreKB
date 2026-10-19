<div class="container">
    <header>
        <h1>Settings</h1>
    </header>
    
    <?php if ($user['group'] !== 'admin'): ?>
        <div class="alert alert-danger" role="alert">You do not have permission to access this page.</div>
    <?php else: ?>
        <main>
            <form action="<?php echo $basePath; ?>/admin/settings" method="POST">
                <?php echo Csrf::field(); ?>
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
                    <h5>Read-only API</h5>
                    <div class="row">
                        <div class="col">
                            <div class="mb-3">
                                <label for="api_enabled" class="form-label">API</label>
                                <?php $apiEnabled = $this->getSetting('api_enabled', 'disabled'); ?>
                                <select class="form-select" id="api_enabled" name="api_enabled">
                                    <option value="disabled" <?php echo ($apiEnabled !== 'enabled') ? 'selected' : ''; ?>>Disabled</option>
                                    <option value="enabled" <?php echo ($apiEnabled === 'enabled') ? 'selected' : ''; ?>>Enabled</option>
                                </select>
                                <div class="form-text">Disabled by default. When enabled, third-party apps can read articles and categories that the public site already shows. Nothing here exposes users, settings, or logs.</div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="api_key" class="form-label">API Key</label>
                                <input type="text" class="form-control" id="api_key" name="api_key" maxlength="128" autocomplete="off" spellcheck="false" value="<?php echo htmlspecialchars($this->getSetting('api_key', ''), ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="form-text">Optional. Leave this blank for no key. If a key is set, clients must send it in the X-API-Key header or the api_key query parameter. With no key, the API is available only while the knowledge base is public. A private knowledge base always requires a key. The key may be 16 to 128 letters, numbers, periods, underscores, tildes, or hyphens.</div>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="mb-3">
                                <button type="submit" class="btn btn-outline-secondary" name="generate_api_key" value="1" onclick="return confirm('Replace the current API key? Clients using the old key will stop working.');">Generate new key</button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="api_cors_origin" class="form-label">API CORS Origin</label>
                                <?php
                                    $apiCors = $this->getSetting('api_cors_origin', '*');
                                    if ($apiCors === '') {
                                        $apiCors = '*';
                                    }
                                ?>
                                <input type="text" class="form-control" id="api_cors_origin" name="api_cors_origin" maxlength="255" value="<?php echo htmlspecialchars($apiCors, ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="form-text">Sent as Access-Control-Allow-Origin. Use * to allow any origin, or one origin such as https://app.example.com.</div>
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
