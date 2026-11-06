<?php
$editorStored = (new Setting())->getValue('editor_toolbar');
$editorDisplay = EditorToolbar::displayOrder($editorStored);
$editorEnabled = array_flip(EditorToolbar::buttonsFromStored($editorStored));
$editorCatalog = EditorToolbar::catalog();
$editorSection = '';
$editorOrder = 0;
?>
<form action="<?php echo $basePath; ?>/admin/settings/editor" method="POST">
    <?php echo Csrf::field(); ?>
    <div class="settings-section">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
            <h2 class="h4 mb-0">Editor</h2>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="editor-toolbar-reset">Reset to default</button>
        </div>
        <p class="text-muted">Customize the editor toolbar</p>

        <div class="mb-2 fw-semibold">Preview</div>
        <div class="tb-preview mb-2" id="editor-toolbar-preview" aria-hidden="true"></div>

        <div class="list-group mb-3" id="editor-toolbar-list">
            <?php foreach ($editorDisplay as $id): ?>
                <?php
                    $button = $editorCatalog[$id];
                    $checked = isset($editorEnabled[$id]);
                    if ($button['section'] !== $editorSection):
                        $editorSection = $button['section'];
                ?>
                    <div class="list-group-item tb-group" data-section="<?php echo htmlspecialchars($button['section']); ?>"><?php echo htmlspecialchars($button['section']); ?></div>
                <?php endif; ?>
                <div class="list-group-item tb-item" data-id="<?php echo htmlspecialchars($id); ?>" data-group="<?php echo htmlspecialchars($button['group']); ?>" data-section="<?php echo htmlspecialchars($button['section']); ?>" data-order="<?php echo (int)$editorOrder; ?>" data-default="<?php echo $button['default'] ? '1' : '0'; ?>" data-preview="<?php echo htmlspecialchars($button['preview']); ?>" data-icon="<?php echo htmlspecialchars($button['icon']); ?>" data-preview-text="<?php echo htmlspecialchars($button['preview'] === 'wide' ? $button['icon'] : ''); ?>">
                    <div class="tb-row">
                        <button type="button" class="tb-grip" draggable="true" aria-label="Drag <?php echo htmlspecialchars($button['label']); ?>"><i class="bi bi-grip-vertical"></i></button>
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" id="toolbar-<?php echo htmlspecialchars($id); ?>" name="toolbar[]" value="<?php echo htmlspecialchars($id); ?>"<?php echo $checked ? ' checked' : ''; ?>>
                            <label class="form-check-label" for="toolbar-<?php echo htmlspecialchars($id); ?>">
                                <?php echo htmlspecialchars($button['label']); ?>
                            </label>
                        </div>
                        <span class="tb-move">
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-move="up" aria-label="Move <?php echo htmlspecialchars($button['label']); ?> up"><i class="bi bi-chevron-up"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-move="down" aria-label="Move <?php echo htmlspecialchars($button['label']); ?> down"><i class="bi bi-chevron-down"></i></button>
                        </span>
                    </div>
                </div>
                <?php $editorOrder++; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="settings-section">
        <button type="submit" class="btn btn-dark">Save editor</button>
    </div>
</form>
<script src="<?php echo $basePath; ?>/js/editor-toolbar.js"></script>
