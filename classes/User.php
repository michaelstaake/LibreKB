<?php

class User extends Database {

    public function checkLogin($email,$password) {

        $query = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->connect()->prepare($query);
        $stmt->execute(['email' => $email]);
        $result = $stmt->fetch();

        if ($result && password_verify($password, $result['password'])) {
            return true;
        } else {
            return false;
        }
    }

    public function getUserData($email, $attribute) {
        if ($attribute !== 'password') {
            $query = "SELECT $attribute FROM users WHERE email = :email";
            $stmt = $this->connect()->prepare($query);
            $stmt->execute(['email' => $email]);
            $result = $stmt->fetch();
            return $result[$attribute];
        }
    }
}