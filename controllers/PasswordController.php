<?php

class PasswordController extends Controller
{
    private $userModel;
    private $emailModel;
    
    public function __construct()
    {
        $this->userModel = new User();
        $this->emailModel = new Email();
    }
    
    public function reset()
    {
        // If already logged in, redirect based on user role
        if (isset($_SESSION['user_id'])) {
            $user = $this->getUser();
            if ($user && ($user['group'] === 'admin' || $user['group'] === 'manager')) {
                return $this->redirect('/admin');
            } else {
                return $this->redirect('/');
            }
        }
        
        if ($this->isPost()) {
            return $this->sendResetEmail();
        }
        
        $data = [
            'pageTitle' => 'Reset Password',
            'message' => $this->getMessage(),
            'error' => $this->getError()
        ];
        
        return $this->view('password-reset', $data);
    }
    
    public function sendResetEmail()
    {
        $email = $_POST['email'] ?? '';
        
        // Always show success message for security (don't reveal if email exists)
        $message = 'If an account exists with that email address, you have been emailed a link to set a new password. This link expires in an hour. If you do not receive the email soon, you can try another reset below.';
        
        // Check if user exists
        $user = $this->userModel->getEnabledByEmail($email);
        if (!$user) {
            // Don't reveal that email doesn't exist, just redirect with success message
            return $this->redirectWithMessage('/password/reset', $message);
        }
        
        // Generate reset token
        $token = $this->userModel->setResetToken($email);
        if (!$token) {
            return $this->redirectWithError('/password/reset', 'There was a problem generating the reset token. Please try again later.');
        }
        
        // Send reset email
        try {
            $emailModel = new Email();
            $emailSent = $emailModel->sendPasswordReset($email, $token);
            if (!$emailSent) {
                $this->setError('There was a problem sending the password reset email. Please try again later or contact your system administrator.');
            }
        } catch (Exception $e) {
            $this->setError('There was a problem sending the password reset email. Please try again later or contact your system administrator.');
        }
        
        return $this->redirectWithMessage('/password/reset', $message);
    }
    
    public function newPassword()
    {
        // If already logged in, redirect based on user role
        if (isset($_SESSION['user_id'])) {
            $user = $this->getUser();
            if ($user && ($user['group'] === 'admin' || $user['group'] === 'manager')) {
                return $this->redirect('/admin');
            } else {
                return $this->redirect('/');
            }
        }
        
        $token = $_GET['token'] ?? '';
        if (empty($token)) {
            return $this->redirectWithError('/password/reset', 'Token missing. Please request a new password reset.');
        }
        
        // Verify token is valid
        $user = $this->userModel->verifyResetToken($token);
        if (!$user) {
            return $this->redirectWithError('/password/reset', 'Token invalid or expired. Please request a new password reset.');
        }
        
        if ($this->isPost()) {
            return $this->updatePassword();
        }
        
        $data = [
            'pageTitle' => 'Set New Password',
            'token' => $token,
            'message' => $this->getMessage(),
            'error' => $this->getError()
        ];
        
        return $this->view('password-new', $data);
    }
    
    public function updatePassword()
    {
        $token = $_POST['token'] ?? $_GET['token'] ?? '';
        if (empty($token)) {
            return $this->redirectWithError('/password/reset', 'Token missing. Please request a new password reset.');
        }
        
        $password1 = $_POST['password1'] ?? '';
        $password2 = $_POST['password2'] ?? '';
        
        // Validate passwords
        if (empty($password1) || empty($password2)) {
            return $this->redirectWithError('/password/new?token=' . urlencode($token), 'Please enter both password fields.');
        }
        
        // Verify passwords match
        if ($password1 !== $password2) {
            return $this->redirectWithError('/password/new?token=' . urlencode($token), 'Passwords don\'t match, please try again.');
        }
        
        // Verify token is still valid
        $user = $this->userModel->verifyResetToken($token);
        if (!$user) {
            return $this->redirectWithError('/password/reset', 'Token invalid or expired. Please request a new password reset.');
        }
        
        // Update password
        $result = $this->userModel->updatePasswordByToken($token, $password1);
        if ($result) {
            $successMessage = 'Your password has been updated successfully. You can now log in with your new password.';
            return $this->redirectWithMessage('/login', $successMessage);
        } else {
            return $this->redirectWithError('/password/new?token=' . urlencode($token), 'There was a problem updating your password. Please try again.');
        }
    }
}
