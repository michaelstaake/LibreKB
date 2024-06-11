<?php

class User extends Database {

    public function checkLogin($email,$password) {

        $query = "SELECT * FROM users WHERE email = :email AND status = 'enabled'";
        $stmt = $this->connect()->prepare($query);
        $stmt->execute(['email' => $email]);
        $result = $stmt->fetch();

        if ($result && password_verify($password, $result['password'])) {
            return true;
        } else {
            return false;
        }
    }

    public function getUser($id) {
        $query = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->connect()->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            return $user;
        } else {
            return false;
        }
    }

    public function getUserData($id, $attribute) {
        if ($attribute !== 'password') {
            $query = "SELECT `$attribute` FROM users WHERE id = :id";
            $stmt = $this->connect()->prepare($query);
            $stmt->execute(['id' => $id]);
            $result = $stmt->fetch();
            return $result[$attribute];
        }
    }

    public function getUserDataByEmail($email, $attribute) {
        if ($attribute !== 'password') {
            $query = "SELECT `$attribute` FROM users WHERE email = :email";
            $stmt = $this->connect()->prepare($query);
            $stmt->execute(['email' => $email]);
            $result = $stmt->fetch();
            return $result[$attribute];
        }
    }

    public function getAllUsers() {
        $query = "SELECT * FROM users";
        $stmt = $this->connect()->prepare($query);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $users;
    }

    public function getAllUsersExcept($currentuser) {
        $query = "SELECT * FROM users WHERE id != :id";
        $stmt = $this->connect()->prepare($query);
        $stmt->execute(['id' => $currentuser]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $users;
    }

    public function doPasswordReset($email) {

        $query = "SELECT * FROM users WHERE email = :email AND status = 'enabled'";
        $stmt = $this->connect()->prepare($query);
        $stmt->execute(['email' => $email]);
        $result = $stmt->fetch();

        if ($result) {
            $token = $email . "-". bin2hex(random_bytes(16));
            $exp = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $query = "UPDATE users SET pw_reset_key = :pw_reset_key, pw_reset_exp = :pw_reset_exp WHERE email = :email";
            $stmt = $this->connect()->prepare($query);
            $stmt->execute(['pw_reset_key' => $token, 'pw_reset_exp' => $exp, 'email' => $email]);
            
            $resetmail = new Email();
            $emailed = $resetmail->sendPasswordReset($email, $token);

            if ($emailed) {
                header('Location: reset.php?msg=resetsubmitted');
                exit;
            } else {
                header('Location: reset.php?msg=emailerror');
                exit;
            }
        } else {
            header('Location: reset.php?msg=resetsubmitted');
            exit;
        }
    }

    public function checkResetToken($token) {
        $query = "SELECT * FROM users WHERE pw_reset_key = :pw_reset_key AND pw_reset_exp >= NOW()";
        $stmt = $this->connect()->prepare($query);
        $stmt->execute(['pw_reset_key' => $token]);
        $result = $stmt->fetch();

        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function updatePassword($token, $password) {
        $query = "UPDATE users SET password = :password, pw_reset_key = NULL, pw_reset_exp = NULL WHERE pw_reset_key = :pw_reset_key";

        try {
            $stmt = $this->connect()->prepare($query);
            $stmt->execute(['password' => password_hash($password, PASSWORD_DEFAULT), 'pw_reset_key' => $token]);
            return true;
        } catch (PDOException $e) {
            //die("Error: " . $e->getMessage());
            return false;
        }
    }

    public function updatePasswordById($id, $password) {
        $query = "UPDATE users SET password = :password, pw_reset_key = NULL, pw_reset_exp = NULL WHERE id = :id";

        try {
            $stmt = $this->connect()->prepare($query);
            $stmt->execute(['password' => password_hash($password, PASSWORD_DEFAULT), 'id' => $id]);
            return true;
        } catch (PDOException $e) {
            //die("Error: " . $e->getMessage());
            return false;
        }
    }

    public function createUser() {
        $email = $this->email;
        $password = $this->password;
        $group = $this->group;
        $status = $this->status;
        $created = date('Y-m-d H:i:s');

        $query = "SELECT COUNT(*) FROM users WHERE email = :email";
        $stmt = $this->connect()->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            return false;
        } else {
            $query = "INSERT INTO users (`email`, `password`, `group`, `status`, `created`) VALUES (:email, :password, :group, :status, :created)";

            try {
                $stmt = $this->connect()->prepare($query);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $password);
                $stmt->bindParam(':group', $group);
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':created', $created);
                $stmt->execute();
                return true;
            } catch (PDOException $e) {
                return false;
            }
        }

        
    }

    public function updateUser() {
        $id = $this->id;
        $email = $this->email;
        $status = $this->status;

        $query = "UPDATE users SET `email` = :email, `status` = :status WHERE `id` = :id";

        try {
            $stmt = $this->connect()->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':status', $status);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            //die("Error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteUser($id) {

        $query = "DELETE FROM users WHERE id = :id";
        try {
            $stmt = $this->connect()->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            //die("Error: " . $e->getMessage());
            return false;
        }
       
    }
}