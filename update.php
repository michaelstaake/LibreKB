<?php
$version = "1.3.1";
$oldVersion = "1.3.1";
require_once('config.php');
$pageTitle = 'LibreKB Updater';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = new Database();
    $conn = $conn->connect();
    try {
        $selectSettingsQuery = "SELECT value FROM settings WHERE name = 'version'";
        $stmt = $conn->prepare($selectSettingsQuery);
        $stmt->execute();
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($settings['value'] == $oldVersion) {
            // This is only for 1.2.2 to 1.3.0 to add parent column to categories table
            $category = new Category($conn);
            $category->upgradeDBto130();
            // continue with the update
            $updateSettingsQuery = "UPDATE settings SET value = :version WHERE name = 'version'";
            $stmt = $conn->prepare($updateSettingsQuery);
            $stmt->bindValue(':version', $version);
            $stmt->execute();
            header('Location: update.php?msg=success');
            exit;
        } else {
            header('Location: update.php?msg=error-version');
            exit;
        }
    } catch (PDOException $e) {
        header('Location: update.php?msg=error');
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
        <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
        <link href="css/login.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <div class="container">
            <br />
            
            <div class="row justify-content-center">
                <div class="col-md-4">
                    <?php
                        if (isset($_GET['msg']) && $_GET['msg'] == 'error-version') {
                            echo '<div class="alert alert-danger" role="alert">This version of the updater can only update '.$oldVersion.' but your database is a different version. Updates must be done sequentially!</div>';
                        }
                        if (isset($_GET['msg']) && $_GET['msg'] == 'error-user') {
                            echo '<div class="alert alert-danger" role="alert">Something went wrong with the update.</div>';
                        }
                        if (isset($_GET['msg']) && $_GET['msg'] == 'success') {
                            echo '<div class="alert alert-success" role="alert">Update complete. Please delete the update.php file then go to <a href="./admin/">admin</a> to continue. There are no files to delete in this update.</div>';
                            exit;
                        }
                        
                    ?>
                    <h1>LibreKB Update</h1>
                    <p>Welcome to LibreKB. This will update version <?php echo $oldVersion; ?> to <?php echo $version; ?></p>
                    <p>Before you begin, please make sure you have uploaded the latest files, over-writing any previous versions. Be sure to retain your database details from your old config.php, put it into the new config.example.php, and renamed it to config.php.</p>
                    <p>For more information, to report bugs, or to get the latest version, go to <a href="https://librekb.com/" target="_blank">librekb.com</a>.</p>
                    <br />
                    <form action="update.php" method="POST">
                        <br />
                        <button type="submit" class="btn btn-dark">Run Updater</button>
                        <br />
                    </form>
                </div>
            </div>
            <br />
        </div>
        <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
?>