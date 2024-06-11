<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo($pageTitle); ?> - LibreKB</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css">
        <link href="../css/admin.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="../js/tinymce/tinymce.min.js"></script>
        <script type="text/javascript">
           tinymce.init({
                selector: '.tinymce',
                plugins: 'lists link code', 
                toolbar: ' bold italic underline | blocks | fontsize | bullist numlist link code'
            });
        </script>
    </head>
    <body>
        <?php
            if (file_exists('../install.php') || file_exists('../upgrade.php')) {
                echo '<br /><div class="container"><div class="alert alert-danger" role="alert">install.php and/or update.php file detected. Please delete those files before using LibreKB.</div></div>';
                exit;
            }
        ?>
        <div class="sub-header">
            <div class="container">
                <?php
                    $user = new User();
                    $user_id = $_SESSION['user_id'];
                    $user_email = $user->getUserData($user_id, 'email');
                ?>    
                <p>Welcome back, <a href="profile.php"><i class="bi bi-person-circle"></i><?php echo $user_email; ?></a></p>
            </div>
        </div>
        <nav>
            <div class="container nav-container">
                <div class="logo">
                    <a href="index.php">LibreKB</a>
                </div>
                <ul>
                    <li><a href="index.php"><i class="bi bi-house-door"></i> Dashboard</a></li>
                    <?php
                        $user = new User();
                        $user_id = $_SESSION['user_id'];
                        $user_group = $user->getUserData($user_id, 'group');

                        if ($user_group == 'admin') {
                            echo '<li><a href="users.php" ><i class="bi bi-people"></i> Users</a></li>';
                            echo '<li><a href="settings.php" ><i class="bi bi-gear"></i> Settings</a></li>';
                        }
                    ?>
                    <li><a href="logout.php" ><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                    
                </ul>
            </div>
        </nav>
        <?php
            $setting = new Setting();
            $maintenanceMode = $setting->getSettingValue('maintenance_mode');
            if ($maintenanceMode == 'enabled') {
                echo '<div class="container"><div class="alert alert-warning" role="alert">Maintenance mode is enabled, and the front end of your knowledge base has been disabled. Go to <a href="settings.php">Settings</a> to manage this.</div></div>';
            }
        ?>
        