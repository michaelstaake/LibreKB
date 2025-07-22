<?php

class UserController extends Controller
{
    private $userModel;
    
    public function __construct()
    {
        $this->userModel = new User();
    }
    
    public function index()
    {
        $user = $this->getUser();
        
        // Only admin can manage users
        if ($user['group'] !== 'admin') {
            return $this->show403();
        }
        
        $users = $this->userModel->getAllExcept($user['id']);
        
        $data = [
            'pageTitle' => 'Users',
            'pageCategory' => 'Users',
            'user' => $user,
            'users' => $users,
            'message' => $this->getMessage(),
            'error' => $this->getError()
        ];
        
        return $this->layout('admin', 'admin/users', $data);
    }
    
    public function create()
    {
        $user = $this->getUser();
        
        // Only admin can create users
        if ($user['group'] !== 'admin') {
            return $this->show403();
        }
        
        if ($this->isPost()) {
            return $this->store();
        }
        
        $data = [
            'pageTitle' => 'Create User',
            'pageCategory' => 'Users',
            'user' => $user,
            'message' => $this->getMessage(),
            'error' => $this->getError()
        ];
        
        return $this->layout('admin', 'admin/users-create', $data);
    }
    
    public function store()
    {
        $name = $this->input('name');
        $email = $this->input('email');
        $password = $this->input('password');
        $group = $this->input('group');
        $status = $this->input('status');
        
        // Check if user already exists
        if ($this->userModel->getByEmail($email)) {
            $this->setError('A user with this email already exists.');
            return $this->redirect('/admin/users/create');
        }
        
        $userData = [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'group' => $group,
            'status' => $status,
            'timezone' => 'timezone'
        ];
        
        if ($this->userModel->createUser($userData)) {
            // Log user creation
            $currentUser = $this->getUser();
            Log::logAction($currentUser['id'], $currentUser['email'], 'created', 'user');
            
            $this->setMessage('User created successfully.');
            return $this->redirect('/admin/users');
        } else {
            $this->setError('Error creating user.');
            return $this->redirect('/admin/users/create');
        }
    }
    
    public function edit($id)
    {
        $currentUser = $this->getUser();
        
        // Only admin can edit users
        if ($currentUser['group'] !== 'admin') {
            return $this->show403();
        }
        
        $user = $this->userModel->find($id);
        if (!$user) {
            $this->setError('User not found.');
            return $this->redirect('/admin/users');
        }
        
        if ($this->isPost()) {
            return $this->update($id);
        }
        
        $data = [
            'pageTitle' => 'Edit User',
            'pageCategory' => 'Users',
            'user' => $currentUser,
            'editUser' => $user,
            'message' => $this->getMessage(),
            'error' => $this->getError()
        ];
        
        return $this->layout('admin', 'admin/users-edit', $data);
    }
    
    public function show($id)
    {
        return $this->edit($id);
    }
    
    public function update($id)
    {
        $name = $this->input('name');
        $email = $this->input('email');
        $group = $this->input('group');
        $status = $this->input('status');
        
        $updateData = [
            'name' => $name,
            'email' => $email,
            'group' => $group,
            'status' => $status
        ];
        
        // Update password if provided
        $password = $this->input('password');
        if (!empty($password)) {
            $updateData['password'] = password_hash($password, PASSWORD_DEFAULT);
        }
        
        if ($this->userModel->update($id, $updateData)) {
            // Log user update
            $currentUser = $this->getUser();
            Log::logAction($currentUser['id'], $currentUser['email'], 'updated', 'user');
            
            $this->setMessage('User updated successfully.');
            return $this->redirect('/admin/users');
        } else {
            $this->setError('Error updating user.');
            return $this->redirect('/admin/users/edit/' . $id);
        }
    }
    
    public function delete($id)
    {
        $currentUser = $this->getUser();
        
        // Only admin can delete users
        if ($currentUser['group'] !== 'admin') {
            return $this->show403();
        }
        
        // Don't allow deleting own account
        if ($id == $currentUser['id']) {
            $this->setError('You cannot delete your own account.');
            return $this->redirect('/admin/users');
        }
        
        if ($this->userModel->delete($id)) {
            // Log user deletion
            Log::logAction($currentUser['id'], $currentUser['email'], 'deleted', 'user');
            
            $this->setMessage('User deleted successfully.');
            return $this->redirect('/admin/users');
        } else {
            $this->setError('Error deleting user. Please try again.');
            return $this->redirect('/admin/users');
        }
    }
}
