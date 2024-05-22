<?php

$version = "0.0.1";

require_once('config.php');
$pageTitle = 'LibreKB Installer';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $password = password_hash($password, PASSWORD_DEFAULT);

    $conn = new Database();
    $conn = $conn->connect();

    try {
        
        // Create tables in the database
        $createTablesQuery = "CREATE TABLE users (
            `id` INT(6) AUTO_INCREMENT PRIMARY KEY,
            `email` VARCHAR(255) NOT NULL,
            `password` VARCHAR(255) NOT NULL,
            `group` VARCHAR(255) NOT NULL,
            `status` VARCHAR(255) NOT NULL,
            `timezone` VARCHAR(255) NOT NULL,
            `pw_reset_key` VARCHAR(255),
            `pw_reset_exp` VARCHAR(255),
            `created` VARCHAR(255)
        );
        
        CREATE TABLE settings (
            `name` VARCHAR(255) PRIMARY KEY,
            `value` VARCHAR(255)
        );
        
        CREATE TABLE articles (
            `id` INT(6) AUTO_INCREMENT PRIMARY KEY,
            `number` INT(6) NOT NULL,
            `title` VARCHAR(255) NOT NULL,
            `slug` VARCHAR(255) NOT NULL,
            `category` VARCHAR(255) NOT NULL,
            `content` LONGTEXT NOT NULL,
            `order` VARCHAR(255) NOT NULL,
            `status` VARCHAR(255) NOT NULL,
            `featured` INT(6) NOT NULL,
            `created` VARCHAR(255) NOT NULL,
            `updated` VARCHAR(255) NOT NULL
        );
        
        CREATE TABLE categories (
            `id` INT(6) AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL,
            `slug` VARCHAR(255) NOT NULL,
            `description` LONGTEXT,
            `icon` VARCHAR(255),
            `order` VARCHAR(255) NOT NULL,
            `status` VARCHAR(255) NOT NULL,
            `created` VARCHAR(255) NOT NULL,
            `updated` VARCHAR(255) NOT NULL
        );
        
        CREATE TABLE log (
            `id` INT(6) AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT(6) NOT NULL,
            `user_email` VARCHAR(255) NOT NULL,
            `performed_action` VARCHAR(255) NOT NULL,
            `on_what` VARCHAR(255) NOT NULL,
            `when` VARCHAR(255) NOT NULL
        );";

        $conn->exec($createTablesQuery);
        $tablesCreated = true;
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
        $tablesCreated = false;
    }

    if ($tablesCreated) {
        // Insert data into settings table
        $insertSettingsQuery = "INSERT INTO settings (name, value) VALUES (:name, :value)";
        $settingsData = [
            ['name' => 'version', 'value' => $version],
            ['name' => 'site_name', 'value' => ''],
            ['name' => 'maintenance_mode', 'value' => ''],
            ['name' => 'maintenance_message', 'value' => ''],
            ['name' => 'site_color', 'value' => ''],
            ['name' => 'site_logo', 'value' => '']
        ];

        try {
            $stmt = $conn->prepare($insertSettingsQuery);
            foreach ($settingsData as $data) {
                $stmt->execute($data);
            }
            $settingsCreated = true;
        } catch (PDOException $e) {
            $settingsCreated = false;
        }
    } else {
        header('Location: install.php?msg=error-tables');
        exit;
    }

    if ($settingsCreated) {
        // Create admin user in the database
        $createUserQuery = "INSERT INTO users (`email`, `password`, `group`, `status`, `timezone`, `created`) VALUES (:email, :password, 'admin', 'enabled', 'timezone', :created)";
        $userData = [
            'email' => $email,
            'password' => $password,
            'created' => date('Y-m-d H:i:s')
        ];

        try {
            $stmt = $conn->prepare($createUserQuery);
            $stmt->execute($userData);
            $userCreated = true;
        } catch (PDOException $e) {
            $userCreated = false;
        }

        if ($userCreated) {
            $installationComplete = true;
        } else {
            header('Location: install.php?msg=error-user');
            exit;
        }
    } else {
        header('Location: install.php?msg=error-settings');
        exit;
    }

    if ($installationComplete) {
        header('Location: install.php?msg=success');
        exit;
    } else {
        header('Location: install.php?msg=error-user');
        exit;
    }
}

?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo($pageTitle); ?></title>
        <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css">
        <link href="../css/login.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <div class="container">
            <br />
            
            <div class="row justify-content-center">
                <div class="col-md-4">
                    <?php
                        if (isset($_GET['msg']) && $_GET['msg'] == 'error-tables') {
                            echo '<div class="alert alert-danger" role="alert">There was an error creating the database tables. Please check that the database is empty before trying again.</div>';
                        }
                        if (isset($_GET['msg']) && $_GET['msg'] == 'error-settings') {
                            echo '<div class="alert alert-danger" role="alert">There was an error inserting settings into the database. Please check that the database is empty before trying again.</div>';
                        }
                        if (isset($_GET['msg']) && $_GET['msg'] == 'error-user') {
                            echo '<div class="alert alert-danger" role="alert">There was an error creating the admin user. Please check that the database is empty before trying again.</div>';
                        }
                        if (isset($_GET['msg']) && $_GET['msg'] == 'success') {
                            echo '<div class="alert alert-success" role="alert">Installation complete. Please delete the install.php file then go to <a href="./admin/">admin</a> to continue.</div>';
                            exit;
                        }
                        
                    ?>
                    <h1>LibreKB Installer</h1>
                    <p>Welcome to LibreKB. This will install version <?php echo $version; ?></p>
                    <p>Before you begin, please make sure you have put your database information into config.php.</p>
                    <p>For more information, to report bugs, or to get the latest version, go to <a href="https://librekb.com/" target="_blank">librekb.com</a>.</p>
                    <br />
                    <form action="install.php" method="POST">
                        <h3>Create your admin user</h3>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <br />
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <br />
                        <button type="submit" class="btn btn-dark">Run Installer</button>
                        <br />
                    </form>
                </div>
            </div>

            <br />
        </div>
        <script src="../js/bootstrap.bundle.min.js"></script>
    </body>
</html>

?>