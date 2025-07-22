<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Database Setup Required - LibreKB</title>
        <link href="/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
        <link href="/css/other.css" rel="stylesheet" type="text/css">
    </head>
    <body class="error-database">
        <div class="container">
            <div class="error-container">
                <div class="error-header">
                    <div class="error-icon">
                        <i class="bi bi-database-x"></i>
                    </div>
                    <h1 class="error-title">Database Setup Required</h1>
                    <p class="error-subtitle">LibreKB needs to be installed before you can continue.</p>
                </div>
                
                <div class="error-details">
                    <h5>
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Missing Database Tables
                    </h5>
                    <p class="mb-2 text-muted">The following required database tables were not found:</p>
                    <ul class="missing-tables">
                        <?php 
                        // Ensure $missingTables is defined and is an array
                        if (!isset($missingTables) || !is_array($missingTables)) {
                            $missingTables = ['users', 'settings', 'articles', 'categories'];
                        }
                        foreach ($missingTables as $table): 
                        ?>
                            <li><?php echo htmlspecialchars($table); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="solution-box">
                    <h5>
                        <i class="bi bi-lightbulb me-2"></i>
                        Quick Solution
                    </h5>
                    <p class="solution-text">
                        Run the LibreKB installer to automatically create all required database tables and set up your knowledge base.
                    </p>
                    <a href="/install" class="btn btn-success">
                        <i class="bi bi-play-circle me-2"></i>
                        Run Installer
                    </a>
                </div>
                
                <details class="technical-details">
                    <summary>
                        <i class="bi bi-gear me-2"></i>
                        Technical Details
                    </summary>
                    <div class="technical-content">
                        <p><strong>What happened?</strong></p>
                        <p>LibreKB attempted to access the database but couldn't find the required tables. This usually happens when:</p>
                        <ul>
                            <li>LibreKB hasn't been installed yet</li>
                            <li>The database configuration is incorrect</li>
                            <li>The database tables were accidentally deleted</li>
                        </ul>
                        
                        <p class="mt-3"><strong>Required Tables:</strong></p>
                        <ul>
                            <li><code>users</code> - User accounts and authentication</li>
                            <li><code>settings</code> - Application configuration</li>
                            <li><code>articles</code> - Knowledge base articles</li>
                            <li><code>categories</code> - Article organization</li>
                        </ul>
                    </div>
                </details>
            </div>
        </div>
        
        <script src="/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
