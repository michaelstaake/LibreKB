<?php
    require_once('../config.php');
    $pageTitle = 'Login';
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = $_POST['email'];
        $password = $_POST['password'];
        
        $user = new User();
        $login = $user->checkLogin($email, $password);
        
        if ($login) {
            session_start();
            $userObject = new User();
            $userStatus = $userObject->getUserData($email, 'status');
            if ($userStatus == 'enabled') {
                $userID = $userObject->getUserData($email, 'id');
                $_SESSION['user_id'] = $userID;
                header('Location: index.php');
                exit;
            } else {
                header('Location: login.php?msg=loginfailed');
                exit;
            }
        } else {
            header('Location: login.php?msg=loginfailed');
            exit;
        }
    }

    session_start();
    if (isset($_SESSION['user_id'])) {
        header('Location: index.php');
        exit;
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
            <div class="row justify-content-center">
                <div class="col-md-4">
                    <br />

                    <?php
                        if (file_exists('../install.php') || file_exists('../update.php')) {
                            echo '<div class="alert alert-danger" role="alert">install.php and/or update.php file detected. Please delete those files before using LibreKB.</div>';
                            exit;
                        }
                    ?>
                    
                    <?php
                        if (isset($_GET['msg']) && $_GET['msg'] == 'loginfailed') {
                            echo '<div class="alert alert-danger" role="alert">Login failed</div>';
                        }
                    ?>
                    
                    <form action="login.php" method="POST">
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
                        <button type="submit" class="btn btn-dark">Submit</button>
                        <br />
                    </form>
                </div>
            </div>
        </div>
        <script src="../js/bootstrap.bundle.min.js"></script>
    </body>
</html>