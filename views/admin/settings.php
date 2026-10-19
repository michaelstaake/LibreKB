<div class="container">
    <header>
        <h1>Settings</h1>
    </header>

    <?php if ($user['group'] !== 'admin'): ?>
        <div class="alert alert-danger" role="alert">You do not have permission to access this page.</div>
    <?php else: ?>
        <main>
            <div class="d-md-none d-flex flex-wrap gap-2 mb-3" aria-label="Settings sections">
                <?php foreach ($sections as $key => $item): ?>
                    <a class="btn btn-sm <?php echo $key === $section ? 'btn-primary' : 'btn-outline-secondary'; ?>" href="<?php echo $basePath; ?>/admin/settings/<?php echo htmlspecialchars($key); ?>"<?php echo $key === $section ? ' aria-current="page"' : ''; ?>><?php echo htmlspecialchars($item['label']); ?></a>
                <?php endforeach; ?>
            </div>
            <div class="row">
                <div class="col-md-3 d-none d-md-block">
                    <nav class="settings-nav list-group" aria-label="Settings sections">
                        <?php foreach ($sections as $key => $item): ?>
                            <a class="list-group-item list-group-item-action<?php echo $key === $section ? ' active' : ''; ?>" href="<?php echo $basePath; ?>/admin/settings/<?php echo htmlspecialchars($key); ?>"<?php echo $key === $section ? ' aria-current="page"' : ''; ?>>
                                <span class="fw-semibold"><?php echo htmlspecialchars($item['label']); ?></span>
                                <small><?php echo htmlspecialchars($item['status']); ?></small>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                </div>
                <div class="col-md-9">
                    <?php include VIEWS_PATH . '/admin/settings/' . $section . '.php'; ?>
                </div>
            </div>
        </main>
    <?php endif; ?>
</div>
