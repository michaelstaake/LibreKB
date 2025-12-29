<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo htmlspecialchars($pageTitle); ?> - <?php echo htmlspecialchars($siteName); ?></title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link href="<?php echo $basePath; ?>/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
        <link href="<?php echo $basePath; ?>/css/kb.css" rel="stylesheet" type="text/css">
        <?php
            $siteColor = $this->getSetting('site_color');
            $navbarColor = !empty($siteColor) ? $siteColor : '#1B1F22';
            echo '<style>';
            echo '.navbar { background-color: '.$navbarColor.' !important; }';
            echo '.navbar .navbar-brand, .navbar .nav-link { color: white !important; }';
            echo '.navbar .nav-link:hover, .navbar .nav-link:focus { color: rgba(255,255,255,0.8) !important; }';
            echo '.navbar .dropdown-toggle:after { border-top-color: white; }';
            echo '</style>';
        ?>
    </head>
    <body>
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container">
                <a class="navbar-brand" href="<?php echo $basePath ?: '/'; ?>">
                    <?php
                        $siteLogo = $this->getSetting('site_logo');
                        if (!empty($siteLogo)) {
                            echo '<img class="kb-logo" src="'.$siteLogo.'" alt="'.$siteName.' Logo" height="32"/>';
                        } else {
                            echo htmlspecialchars($siteName);
                        }
                    ?>
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $basePath ?: '/'; ?>"><i class="bi bi-house-door"></i> Knowledge Base</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $basePath; ?>/search"><i class="bi bi-search"></i> Search</a>
                        </li>
                        <?php 
                        // Show navigation based on login status and user role
                        if (isset($_SESSION['user_id'])) {
                            $userModel = new User();
                            $currentUser = $userModel->getUser($_SESSION['user_id']);
                            if ($currentUser) {
                                // User dropdown for logged-in users
                                echo '<li class="nav-item dropdown">';
                                echo '<a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">';
                                echo '<i class="bi bi-person-circle"></i> My Account';
                                echo '</a>';
                                echo '<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">';
                                echo '<li><a class="dropdown-item" href="'.$basePath.'/profile"><i class="bi bi-person-circle"></i> Profile</a></li>';
                                
                                // Admin link for admin and manager users
                                if ($currentUser['group'] === 'admin' || $currentUser['group'] === 'manager') {
                                    echo '<li><a class="dropdown-item" href="'.$basePath.'/admin"><i class="bi bi-gear"></i> Admin</a></li>';
                                }
                                echo '<li><hr class="dropdown-divider"></li>';
                                echo '<li><a class="dropdown-item" href="'.$basePath.'/logout"><i class="bi bi-box-arrow-right"></i> Logout</a></li>';
                                echo '</ul>';
                                echo '</li>';
                            }
                        } else {
                            echo '<li class="nav-item">';
                            echo '<a class="nav-link" href="'.$basePath.'/login"><i class="bi bi-box-arrow-in-right"></i> Login</a>';
                            echo '</li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </nav>
        
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
                <hr />
                <p>
                    Copyright &copy; <?php echo date("Y"); ?> <?php echo htmlspecialchars($siteName); ?>
                </p>
            </div>
        </footer>
        <script src="<?php echo $basePath; ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
