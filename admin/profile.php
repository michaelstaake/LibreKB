<?php

    session_start();
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    require_once('../config.php');

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = $_POST['email'];
        $user_id = $_POST['user_id'];

        $user = new User();
        $user->id = $user_id;
        $user->email = $email;
        $user->status = "enabled";
        $user->updateUser();
        header('Location: profile.php?msg=success');
        exit;

        
    } else {
        $pageCategory = "Profile";
        $pageTitle = "Profile";
    
        require_once('header.php');

        ?>

            <div class="container">
                <?php
                    if (isset($_GET['msg']) && $_GET['msg'] == 'error') {
                        echo '<div class="alert alert-danger" role="alert">There was an error processing your request.</div>';
                    }
                    if (isset($_GET['msg']) && $_GET['msg'] == 'success') {
                        echo '<div class="alert alert-success" role="alert">Action performed successfully.</div>';
                    }
                ?>
                <header>
                    <h1>Profile</h1>
                </header>
                <main>
                    <div class="profile-page">
                        <p>Manage your user account here.</p>
                        <form action="profile.php" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo $user_email; ?>" required>
                            </div>
                            <input type="hidden" name="user_id" value="<?php echo $user_id ?>">
                            <button type="submit" class="btn btn-dark">Save</button>
                        </form>
                    </div>                   
                </main>
            </div>

        <?php

        require_once('footer.php');
    }
   
?>