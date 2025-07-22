<?php

class ProfileController extends Controller
{
    private $userModel;
    
    public function __construct()
    {
        $this->userModel = new User();
    }
    
    public function show()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            $this->setError('Please log in to access your profile.');
            return $this->redirect('/login');
        }
        
        if ($this->isPost()) {
            return $this->update();
        }
        
        $user = $this->getUser();
        
        if (!$user) {
            $this->setError('Your session has expired. Please log in again.');
            return $this->redirect('/login');
        }
        
        $siteName = $this->getSetting('site_name', 'Knowledge Base');
        
        $data = [
            'pageTitle' => 'Profile',
            'siteName' => $siteName,
            'user' => $user,
            'message' => $this->getMessage(),
            'error' => $this->getError()
        ];
        
        return $this->layout('main', 'profile', $data);
    }
    
    public function update()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            $this->setError('Please log in to access your profile.');
            return $this->redirect('/login');
        }
        
        $user = $this->getUser();
        if (!$user) {
            $this->setError('Your session has expired. Please log in again.');
            return $this->redirect('/login');
        }
        
        $userId = $this->input('user_id');
        $email = $this->input('email');
        
        // Security check: ensure user can only update their own profile
        if ($userId != $user['id']) {
            $this->setError('You can only update your own profile.');
            return $this->redirect('/profile');
        }
        
        // Check if email already exists for another user
        $existingUser = $this->userModel->getByEmail($email);
        if ($existingUser && $existingUser['id'] != $userId) {
            $this->setError('This email address is already in use by another user.');
            return $this->redirect('/profile');
        }
        
        // Prepare update data - only include name for admin/manager users
        $updateData = [
            'email' => $email
        ];
        
        // Only allow name updates for admin and manager users
        if (in_array($user['group'], ['admin', 'manager'])) {
            $name = $this->input('name');
            $updateData['name'] = $name;
        }
        
        if ($this->userModel->update($userId, $updateData)) {
            // Log profile update
            Log::logAction($user['id'], $user['email'], 'updated', 'profile');
            
            $this->setMessage('Profile updated successfully.');
            return $this->redirect('/profile');
        } else {
            $this->setError('Error updating profile. Please try again.');
            return $this->redirect('/profile');
        }
    }
}
