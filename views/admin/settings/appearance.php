<h2 class="h4">Appearance</h2>
<p class="text-muted">Header color, logo, Google Fonts, and custom CSS for the public site. Site name and visibility are on General.</p>
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
    <div class="mb-3">
        <label for="google_fonts_css_url" class="form-label">Google Fonts CSS URL</label>
        <?php $fontUrl = (new Setting())->getValue('google_fonts_css_url'); ?>
        <input type="text" class="form-control<?php echo ($invalidField === 'google_fonts_css_url') ? ' is-invalid' : ''; ?>" id="google_fonts_css_url" name="google_fonts_css_url" value="<?php echo htmlspecialchars($fontUrl, ENT_QUOTES, 'UTF-8'); ?>" maxlength="2000" spellcheck="false" autocomplete="off">
        <?php if ($invalidField === 'google_fonts_css_url' && !empty($error)): ?>
            <div class="invalid-feedback"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <div class="form-text">Paste the CSS URL from Google Fonts (fonts.googleapis.com/css2?...). Then set font-family in Custom CSS. Leave this blank to keep the built-in fonts.</div>
    </div>
    <div class="mb-3">
        <label for="custom_css" class="form-label">Custom CSS</label>
        <p class="text-muted">Optional rules for the public knowledge base. They are added after the built-in styles, the site color, and the Google Fonts stylesheet. Admin pages are not affected. Set font-family here after pasting a Google Fonts CSS URL above.</p>
        <?php $customCss = (new Setting())->getValue('custom_css'); ?>
        <textarea class="form-control font-monospace<?php echo ($invalidField === 'custom_css') ? ' is-invalid' : ''; ?>" id="custom_css" name="custom_css" rows="16" maxlength="20000" spellcheck="false"><?php echo htmlspecialchars($customCss, ENT_QUOTES, 'UTF-8'); ?></textarea>
        <?php if ($invalidField === 'custom_css' && !empty($error)): ?>
            <div class="invalid-feedback"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <div class="form-text">Leave this blank to keep the built-in look. The header color is already marked important, so a navbar rule has to include !important to replace it. Rules cannot contain a less-than character. <span id="custom-css-count"><?php echo (int)CustomCss::length($customCss); ?></span> of 20,000 characters.</div>
    </div>
    <button type="submit" class="btn btn-dark">Save appearance</button>
    <div class="form-text mt-2">Only Appearance is saved. General, Editor, API, and Maintenance stay as they are.</div>
</form>
<script>
(function () {
    var field = document.getElementById('custom_css');
    var count = document.getElementById('custom-css-count');
    if (!field || !count) {
        return;
    }
    field.addEventListener('input', function () {
        count.textContent = String(field.value.length);
    });
})();
</script>
