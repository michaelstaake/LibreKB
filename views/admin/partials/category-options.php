<?php foreach ($categories as $categoryOption): ?>
    <option value="<?php echo (int)$categoryOption['id']; ?>"
            <?php echo (isset($selectedId) && (string)$selectedId === (string)$categoryOption['id']) ? 'selected' : ''; ?>>
        <?php echo str_repeat('— ', (int)$categoryOption['depth']) . htmlspecialchars($categoryOption['name']); ?>
    </option>
<?php endforeach; ?>
