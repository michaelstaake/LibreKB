<?php
require_once('config.php');
$current = new Version();
$pageTitle = 'LibreKB Upgrade';

$basePath = '';
if (class_exists('Config')) {
    $config = new Config();
    if (!empty($config->systemURL)) {
        $basePath = rtrim(parse_url($config->systemURL, PHP_URL_PATH), '/');
    }
}
if ($basePath === '') {
    $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    if ($scriptDir !== '/' && $scriptDir !== '.') {
        $basePath = $scriptDir;
    }
}
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo htmlspecialchars($pageTitle); ?></title>
        <link href="<?php echo $basePath; ?>/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
        <link href="<?php echo $basePath; ?>/css/other.css" rel="stylesheet" type="text/css">
    </head>
    <body class="install-page">
        <div class="container">
            <div class="install-container">
                <div class="install-header">
                    <div class="install-icon">
                        <i class="bi bi-arrow-up-circle"></i>
                    </div>
                    <h1 class="install-title">LibreKB Upgrade</h1>
                    <div class="version-badge">
                        Version <?php echo htmlspecialchars($current->version); ?>
                    </div>
                </div>

                <?php if (isset($error) && !empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($message) && !empty($message)): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($coreMissing)): ?>
                    <div class="info-text">
                        The required LibreKB tables are missing
                        (<?php echo htmlspecialchars(implode(', ', $coreMissing)); ?>).
                        Use the installer for a new database. This upgrade does not create a fresh install and does not delete data.
                    </div>
                    <div class="d-grid">
                        <a href="<?php echo $basePath; ?>/install" class="btn btn-primary">
                            <i class="bi bi-play-circle me-2"></i>Run Installer
                        </a>
                    </div>
                <?php elseif (!empty($upToDate)): ?>
                    <div class="info-text">
                        This database is already up to date for LibreKB <?php echo htmlspecialchars($current->version); ?>. Nothing else needs to change.
                    </div>
                    <div class="d-grid">
                        <a href="<?php echo $basePath; ?>/admin" class="btn btn-primary">
                            <i class="bi bi-speedometer2 me-2"></i>Go to Admin
                        </a>
                    </div>
                <?php else: ?>
                    <div class="info-text">
                        <?php if (!empty($needsAttachments)): ?>
                            This upgrade adds an attachments table so articles can offer file downloads.
                        <?php endif; ?>
                        <?php if (!empty($missingSettings)): ?>
                            This upgrade adds the read-only API settings. The API stays disabled until an administrator enables it.
                        <?php endif; ?>
                        <?php if (!empty($needsSettingsText)): ?>
                            This upgrade changes the settings value column to TEXT so a custom CSS sheet can be stored.
                        <?php endif; ?>
                        <?php if (!empty($missingCustomCss)): ?>
                            This upgrade adds an empty custom CSS setting. It is not applied until an administrator saves CSS under Appearance.
                        <?php endif; ?>
                        <?php if (!empty($missingEditorToolbar)): ?>
                            This upgrade adds an empty editor toolbar setting. An empty value keeps the built-in toolbar.
                        <?php endif; ?>
                        Categories, articles, users, and logs stay as they are. Nothing is deleted or rewritten.
                    </div>
                    <form action="<?php echo $basePath; ?>/upgrade" method="POST">
                        <?php echo Csrf::field(); ?>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-arrow-up-circle me-2"></i>Upgrade Database
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <script src="<?php echo $basePath; ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
