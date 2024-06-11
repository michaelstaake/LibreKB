<?php
    require_once('../config.php');
    $pageTitle = 'Reset Password';

    session_start();
    if (isset($_SESSION['user_id'])) {
        header('Location: index.php');
        exit;
    }

    if (isset($_GET['action']) && $_GET['action'] === 'setnew') {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $token = $_POST['token'];
            $password1 = $_POST['password1'];
            $password2 = $_POST['password2'];

            if ($password1 !== $password2) {
                header('Location: reset.php?action=setnew&msg=passwordsdontmatch&token=' . $token);
                exit;
            } else {
                $user = new User();
                $tokenstatus = $user->checkResetToken($token);

                if ($tokenstatus) {
                    $user = new User();
                    $user->updatePassword($token, $password1);
                    header('Location: login.php?msg=resetdone');
                    exit;
                } else {
                    header('Location: reset.php?action=setnew&msg=tokenbad');
                    exit;
                }
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
                        <div class="row justify-content-center">
                            
                            <div class="col-md-4">
                                <br />
                                
                                <?php
                                    if (isset($_GET['msg']) && $_GET['msg'] == 'tokenbad') {
                                        echo '<div class="alert alert-danger" role="alert">Token missing, invalid or expired.</div>';
                                        exit;
                                    }
                                ?>
                                <?php
                                    if (isset($_GET['msg']) && $_GET['msg'] == 'passwordsdontmatch') {
                                        echo '<div class="alert alert-danger" role="alert">Passwords don\'t match, please try again.</div>';
                                    }
                                ?>

                                <h3>Update Password</h3>
                                <p>Select a new password.</p>
                                
                                <form action="reset.php?action=setnew" method="POST">
                                    <div class="mb-3">
                                        <label for="password1" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="password1" name="password1" autocomplete="new-password" required autofocus>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password2" class="form-label2">Confirm Password</label>
                                        <input type="password" class="form-control" id="password2" name="password2" autocomplete="new-password" required>
                                    </div>
                                    <br />
                                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <button type="submit" class="btn btn-dark" id="submitPassword">Submit</button>
                                    <br />
                                </form>
                            </div>
                        </div>
                    </div>
                    <script src="../js/bootstrap.bundle.min.js"></script>
                </body>
            </html>
        <?php
    } else {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = $_POST['email'];
            
            $user = new User();
            $reset = $user->doPasswordReset($email);
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
                                <br>
                                
                                <?php
                                    if (isset($_GET['msg']) && $_GET['msg'] == 'resetsubmitted') {
                                        echo '<div class="alert alert-primary" role="alert">If an account exists with that email address, you have been emailed a link to set a new password. This link expires in an hour. If you do not receive the email soon, you can try another reset below.</div>';
                                    }
                                    if (isset($_GET['msg']) && $_GET['msg'] == 'emailerror') {
                                        echo '<div class="alert alert-danger" role="alert">There was a problem sending the password reset email. Please try again later or contact your system administrator.</div>';
                                    }
                                ?>

                                <h3>Reset Password</h3>
                                <p>Enter your email address to receive a link to set a new password.</p>
                                
                                <form action="reset.php" method="POST">
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" required autofocus>
                                    </div>
                                    <br />
                                    <button type="submit" class="btn btn-dark">Submit</button>
                                    <br />
                                </form>
                                <br>
                                <a href="login.php">Back to Login</a>
                            </div>
                        </div>
                    </div>
                    <script src="../js/bootstrap.bundle.min.js"></script>
                </body>
            </html>
        <?php
    }
    
?>