<?php

    session_start();
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    require_once('../config.php');

    if (isset($_GET['action']) && $_GET['action'] === 'userCreate') {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = $_POST['email'];
            $password1 = $_POST['password1'];
            $password2 = $_POST['password2'];
            $group = $_POST['group'];
            $status = $_POST['status'];

            if ($password1 !== $password2) {
                header('Location: users.php?msg=error');
                exit;
            } else {
                $user = new User();
                $user->email = $email;
                $user->password = $password1;
                $user->group = $group;
                $user->status = $status;
                $userResult = $user->createUser();
    
                if ($userResult) {
                    header('Location: users.php?msg=success');
                    exit;
                } else {
                    header('Location: users.php?msg=error');
                    exit;
                }
            }

           
            
        }

    } else if (isset($_GET['action']) && $_GET['action'] === 'userManage') {
        if (isset($_GET['u'])) {
            $user = new User();
            $userData = $user->getUser($_GET['u']);
            if (!$userData) {
                header('Location: users.php?msg=error');
                exit;
            } else {
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $email = $_POST['email'];
                    $status = $_POST['status'];
        
                    $user = new User();
                    $user->id = $_GET['u'];
                    $user->email = $email;
                    $user->status = $status;
                    $user->updateUser();
                    header('Location: users.php?msg=success');
                    exit;
                }
            }
        }
    } else if (isset($_GET['action']) && $_GET['action'] === 'userDelete') {
        if (isset($_GET['u'])) {
            $user = new User();
            $userData = $user->getUser($_GET['u']);
            if (!$userData) {
                header('Location: users.php?msg=error');
                exit;
            } else {
                $user = new User();
                $user->deleteUser($_GET['u']);
                header('Location: users.php?msg=success');
                exit;
            }
        }
    } else if (isset($_GET['action']) && $_GET['action'] === 'userPassword') {
        if (isset($_GET['u'])) {
            $user = new User();
            $userData = $user->getUser($_GET['u']);
            if (!$userData) {
                header('Location: users.php?msg=error');
                exit;
            } else {
                $password1 = $_POST['password1'];
                $password2 = $_POST['password2'];
    
                if ($password1 !== $password2) {
                    header('Location: users.php?msg=error');
                    exit;
                } else {
                    $user = new User();
                    $user->updatePasswordById($_GET['u'], $_POST['password1']);
                    header('Location: users.php?msg=success');
                exit;
                }
                
            }
        }
    } else {
        $pageCategory = "Users";
        $pageTitle = "Users";
    
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
                    <h1>Users</h1>
                </header>
                <?php

                    if ($user_group !== 'admin') {
                        echo '<div class="alert alert-danger" role="alert">You do not have permission to access this page.</div>';
                        exit;
                    }  
                ?>
                <main>
                    <div class="this-user">
                        <h3>Your Account</h3>
                        <p>Manage your account on the <a href="profile.php">Profile</a> page.</p>
                    </div>
                    <div class="other-users">
                        <h3>Other Users</h3>
                        <?php
                            $currentuser = $_SESSION['user_id'];
                            $user = new User();
                            $users = $user->getAllUsersExcept($currentuser);

                            foreach($users as $user) {
                                echo '
                                    <div class="user-item">
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#editUser' . $user['id'] . '">
                                            <div class="user-content">
                                                <h6><i class="bi bi-person"></i>  ' . $user['email'] . '</h6>
                                                <p>Group: <code>' . $user['group'] . '</code> Status: <code>' . $user['status'] . '</code> Created: <code>' . $user['created'] . '</code></p>
                                            </div>
                                        </a>
                                    </div>
                                    <!-- Edit User Modal -->
                                    <div class="modal fade" id="editUser' . $user['id'] . '" tabindex="-1" aria-labelledby="editUser' . $user['id'] . 'Label" aria-hidden="true">
                                        <form action="users.php?action=userManage&u=' . $user['id'] . '" method="POST">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="editUser' . $user['id'] . 'Label">' . $user['email'] . '</h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label for="email" class="form-label">Email</label>
                                                            <input type="email" class="form-control" id="email" name="email" value="' . $user['email'] . '" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="status" class="form-label">Status</label>
                                                            <select class="form-select" id="status" name="status">
                                                                <option value="enabled" '.(($user['status']=='enabled')?'selected="selected"':"").'>Enabled</option>
                                                                <option value="disabled" '.(($user['status']=='disabled')?'selected="selected"':"").'>Disabled</option>
                                                            </select>
                                                        </div>
                                                        <div class="user-actions">
                                                            <a href="#" data-bs-toggle="modal" data-bs-target="#passwordUser' . $user['id'] . '">Change Password</a> <a class="user-delete" href="#" data-bs-toggle="modal" data-bs-target="#deleteUser' . $user['id'] . '">Delete User</a>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-primary">Save</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <!-- Delete User Modal -->
                                    <div class="modal fade" id="deleteUser' . $user['id'] . '" tabindex="-1" aria-labelledby="deleteUser' . $user['id'] . 'Label" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="deleteUser' . $user['id'] . 'Label">Delete ' . $user['email'] . '</h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Are you sure you want to delete this user?</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <form action="users.php?action=userDelete&u=' . $user['id'] . '" method="POST">
                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                </form>
                                            </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Password User Modal -->
                                    <div class="modal fade" id="passwordUser' . $user['id'] . '" tabindex="-1" aria-labelledby="passwordUser' . $user['id'] . 'Label" aria-hidden="true">
                                        <form action="users.php?action=userPassword&u=' . $user['id'] . '" method="POST">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="editUser' . $user['id'] . 'Label">' . $user['email'] . '</h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label for="password1" class="form-label">Password</label>
                                                            <input type="password" class="form-control" id="password1" name="password1" autocomplete="new-password" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="password2" class="form-label2">Confirm Password</label>
                                                            <input type="password" class="form-control" id="password2" name="password2" autocomplete="new-password" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-primary">Save</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    ';
                            }
                        ?>
                        <a href="#" data-bs-toggle="modal" data-bs-target="#createUser" class="btn btn-dark">Create User</a>
                        <!-- Create User Modal -->
                        <div class="modal fade" id="createUser" tabindex="-1" aria-labelledby="createUserLabel" aria-hidden="true">
                            <form action="users.php?action=userCreate" method="POST">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h1 class="modal-title fs-5" id="createUserLabel">Create User</h1>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="email" name="email" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="password1" class="form-label">Password</label>
                                                <input type="password" class="form-control" id="password1" name="password1" autocomplete="new-password" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="password2" class="form-label">Confirm Password</label>
                                                <input type="password" class="form-control" id="password2" name="password2" autocomplete="new-password" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="group" class="form-label">Group</label>
                                                <select class="form-select" id="group" name="group">
                                                    <option value="user">User</option>
                                                    <option value="admin">Admin</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="status" class="form-label">Status</label>
                                                <select class="form-select" id="status" name="status">
                                                    <option value="enabled">Enabled</option>
                                                    <option value="disabled">Disabled</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Save</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                   
                </main>
            </div>

        <?php

        require_once('footer.php');
    }
   
?>