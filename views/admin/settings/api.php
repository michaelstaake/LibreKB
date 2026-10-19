<h2 class="h4">API</h2>
<p class="text-muted">Read-only access for other apps.</p>
<form action="<?php echo $basePath; ?>/admin/settings/api" method="POST" class="d-flex flex-column">
    <?php echo Csrf::field(); ?>
    <button type="submit" class="btn btn-dark order-last align-self-start">Save API settings</button>
    <div class="form-text order-last mt-2">Only the API section is saved. Generating a key does not rewrite the other sections.</div>
    <div class="mb-3">
        <label for="api_enabled" class="form-label">API</label>
        <?php $apiEnabled = $this->getSetting('api_enabled', 'disabled'); ?>
        <select class="form-select<?php echo ($invalidField === 'api_enabled') ? ' is-invalid' : ''; ?>" id="api_enabled" name="api_enabled">
            <option value="disabled" <?php echo ($apiEnabled !== 'enabled') ? 'selected' : ''; ?>>Disabled</option>
            <option value="enabled" <?php echo ($apiEnabled === 'enabled') ? 'selected' : ''; ?>>Enabled</option>
        </select>
        <?php if ($invalidField === 'api_enabled' && !empty($error)): ?>
            <div class="invalid-feedback"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <div class="form-text">Disabled by default. When enabled, third-party apps can read articles and categories that the public site already shows. Nothing here exposes users, settings, or logs.</div>
    </div>
    <div class="mb-3">
        <label for="api_key" class="form-label">API Key</label>
        <div class="d-flex flex-wrap gap-2">
            <input type="text" class="form-control<?php echo ($invalidField === 'api_key') ? ' is-invalid' : ''; ?>" id="api_key" name="api_key" maxlength="128" autocomplete="off" spellcheck="false" value="<?php echo htmlspecialchars($this->getSetting('api_key', ''), ENT_QUOTES, 'UTF-8'); ?>" style="flex: 1 1 16rem; min-width: 0;">
            <button type="submit" class="btn btn-outline-secondary" name="generate_api_key" value="1" onclick="return confirm('Replace the current API key? Clients using the old key will stop working.');">Generate new key</button>
        </div>
        <?php if ($invalidField === 'api_key' && !empty($error)): ?>
            <div class="invalid-feedback d-block"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <div class="form-text">Optional. Leave this blank for no key. If a key is set, clients must send it in the X-API-Key header or the api_key query parameter. With no key, the API is available only while the knowledge base is public. A private knowledge base always requires a key. The key may be 16 to 128 letters, numbers, periods, underscores, tildes, or hyphens.</div>
    </div>
    <div class="mb-3">
        <label for="api_cors_origin" class="form-label">API CORS Origin</label>
        <?php
            $apiCors = $this->getSetting('api_cors_origin', '*');
            if ($apiCors === '') {
                $apiCors = '*';
            }
        ?>
        <input type="text" class="form-control<?php echo ($invalidField === 'api_cors_origin') ? ' is-invalid' : ''; ?>" id="api_cors_origin" name="api_cors_origin" maxlength="255" value="<?php echo htmlspecialchars($apiCors, ENT_QUOTES, 'UTF-8'); ?>">
        <?php if ($invalidField === 'api_cors_origin' && !empty($error)): ?>
            <div class="invalid-feedback"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <div class="form-text">Sent as Access-Control-Allow-Origin. Use * to allow any origin, or one origin such as https://app.example.com.</div>
    </div>
</form>
