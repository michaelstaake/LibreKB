<?php

class User extends Model
{
    protected $table = 'users';
    
    public function authenticate($email, $password)
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email AND status = 'enabled'";
        $user = $this->fetchOne($sql, ['email' => $email]);
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }

    public function getUser($id)
    {
        return $this->findBy('id', $id);
    }
    
    public function getByEmail($email)
    {
        return $this->findBy('email', $email);
    }
    
    public function getEnabledByEmail($email)
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email AND status = 'enabled'";
        return $this->fetchOne($sql, ['email' => $email]);
    }
    
    public function getAllExcept($userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id != :userId";
        return $this->fetchAll($sql, ['userId' => $userId]);
    }
    
    public function createUser($data)
    {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['created'] = date('Y-m-d H:i:s');
        return $this->create($data);
    }
    
    public function updatePassword($id, $password)
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        return $this->update($id, ['password' => $hashedPassword]);
    }
    
    public function updatePasswordByToken($token, $password)
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE {$this->table} SET password = :password, pw_reset_key = NULL, pw_reset_exp = NULL WHERE pw_reset_key = :token";
        return $this->execute($sql, [
            'password' => $hashedPassword,
            'token' => $token
        ]);
    }
    
    public function setResetToken($email)
    {
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', time() + 3600); // 1 hour from now
        
        $sql = "UPDATE {$this->table} SET pw_reset_key = :token, pw_reset_exp = :expiry WHERE email = :email";
        $result = $this->execute($sql, [
            'token' => $token,
            'expiry' => $expiry,
            'email' => $email
        ]);
        
        return $result ? $token : false;
    }
    
    public function verifyResetToken($token)
    {
        $sql = "SELECT * FROM {$this->table} WHERE pw_reset_key = :token AND pw_reset_exp >= NOW()";
        return $this->fetchOne($sql, ['token' => $token]);
    }
}
