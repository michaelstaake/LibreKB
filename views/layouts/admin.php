<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo htmlspecialchars($pageTitle); ?> - LibreKB</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link href="/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
        <link href="/css/admin.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container">
                <a class="navbar-brand" href="/admin">
                    LibreKB
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="adminNavbar">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="/admin"><i class="bi bi-speedometer2"></i> Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/articles/create"><i class="bi bi-file-earmark-plus"></i> Create Article</a>
                        </li>
                        <?php if ($user && $user['group'] === 'admin'): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-gear"></i> Administration
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                                    <li><a class="dropdown-item" href="/admin/users"><i class="bi bi-people"></i> Manage Users</a></li>
                                    <li><a class="dropdown-item" href="/admin/settings"><i class="bi bi-sliders"></i> Site Settings</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="/admin/logs"><i class="bi bi-clock-history"></i> Activity Log</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                    </ul>
                    
                    <ul class="navbar-nav">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userMenuDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle"></i> My Account
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenuDropdown">
                                <li><a class="dropdown-item" href="/profile"><i class="bi bi-person-circle"></i> Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/logout"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        
        <?php
            if (file_exists(ROOT_PATH . '/install.php') || file_exists(ROOT_PATH . '/update.php')) {
                echo '<div class="container"><div class="alert alert-danger" role="alert">install.php and/or update.php file detected. Please delete those files before using LibreKB.</div></div>';
            }
        ?>
        
        <?php
            $maintenanceMode = $this->getSetting('maintenance_mode');
            if ($maintenanceMode === 'enabled') {
                echo '<div class="container"><div class="alert alert-warning" role="alert">Maintenance mode is enabled, and the front end of your knowledge base has been disabled. Go to <a href="/admin/settings">Settings</a> to manage this.</div></div>';
            }
        ?>
        
        <?php if (isset($error) && !empty($error)) { ?>
            <div class="container"><div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div></div>
        <?php } ?>

        <?php if (isset($message) && !empty($message)) { ?>
            <div class="container"><div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($message); ?>
            </div></div>
        <?php } ?>
        
        <?php echo $content; ?>
        
        <footer>
            <div class="container footer-container">
                <p>
                    <a href="/" target="_blank">Go to Front End</a> &middot; 
                    Powered by <a href="https://librekb.com/" target="_blank">LibreKB</a> 
                    <?php
                        $current = new Version();
                        echo $current->version;
                        if ($current->channel === 'beta') {
                            echo " beta";
                        }
                     ?>
                </p>
            </div>
        </footer>
        <script src="/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
