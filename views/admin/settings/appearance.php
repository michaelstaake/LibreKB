<h2 class="h4">Appearance</h2>
<p class="text-muted">Header color and logo for the public site. Site name and visibility are on General.</p>
<form action="<?php echo $basePath; ?>/admin/settings/appearance" method="POST">
    <?php echo Csrf::field(); ?>
    <div class="row">
        <div class="col-12 col-md-6">
            <div class="mb-3">
                <label for="site_color" class="form-label">Site Color</label>
                <input type="text" class="form-control" id="site_color" name="site_color" value="<?php echo htmlspecialchars($this->getSetting('site_color')); ?>">
                <div class="form-text">If this is blank, the default color of #1B1F22 will be used.</div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="mb-3">
                <label for="site_logo" class="form-label">Site Logo</label>
                <input type="text" class="form-control" id="site_logo" name="site_logo" value="<?php echo htmlspecialchars($this->getSetting('site_logo')); ?>">
                <div class="form-text">If this is blank, the site title will be displayed in the header.</div>
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-dark">Save appearance</button>
    <div class="form-text mt-2">Only Appearance is saved. General, Editor, API, and Maintenance stay as they are.</div>
</form>
