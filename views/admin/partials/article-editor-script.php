<?php
$toolbarButtons = EditorToolbar::buttonsFromStored((new Setting())->getValue('editor_toolbar'));
$toolbarPlugins = EditorToolbar::pluginsFor($toolbarButtons);
$toolbarString = EditorToolbar::toString($toolbarButtons);
$toolbarHasImage = in_array('image', $toolbarButtons, true);
$syncEditorOnSubmit = !empty($syncEditorOnSubmit);
?>
<script src="<?php echo $basePath; ?>/vendor/tinymce/tinymce.min.js"></script>
<script>
tinymce.init({
    selector: '#content',
    license_key: 'gpl',
    plugins: <?php echo json_encode(implode(' ', $toolbarPlugins), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
    toolbar: <?php echo json_encode($toolbarString, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
    toolbar_mode: 'wrap',
    height: 400,
    menubar: false,
    branding: false,
    promotion: false,
    // Leave pasted URLs alone. The default rewrite is relative to this
    // editor page (/admin/articles/...), which is one folder deeper than a
    // public article, so a subdirectory base path such as /docs is lost.
    convert_urls: false,
    content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 14px; }',
    block_formats: 'Paragraph=p; Header 1=h1; Header 2=h2; Header 3=h3; Header 4=h4; Header 5=h5; Header 6=h6; Preformatted=pre',
    fontsize_formats: '8pt 9pt 10pt 11pt 12pt 14pt 16pt 18pt 20pt 22pt 24pt 26pt 28pt 36pt 48pt 72pt',
    paste_data_images: false<?php if ($toolbarHasImage): ?>,
    image_caption: false,
    image_advtab: false,
    image_uploadtab: false,
    automatic_uploads: false<?php endif; ?><?php if ($syncEditorOnSubmit): ?>,
    setup: function (editor) {
        editor.on('change', function () {
            editor.save();
        });
    }<?php endif; ?>
});
<?php if ($syncEditorOnSubmit): ?>
(function () {
    var content = document.getElementById('content');
    if (!content || !content.form) {
        return;
    }
    content.form.addEventListener('submit', function () {
        tinymce.triggerSave();
    });
})();
<?php endif; ?>
</script>
