<div class="container">
    
    <main>
        <?php if ($user['group'] !== 'admin'): ?>
            <div class="alert alert-danger" role="alert">You do not have permission to access this page.</div>
        <?php else: ?>
            <header>
                <div>
                    <h1>Edit User</h1>
                </div>
                <hr>
            </header>
            <form action="/admin/users/<?php echo $editUser['id']; ?>" method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($editUser['name'] ?? ''); ?>" placeholder="Name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($editUser['email']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password">
                            <small class="form-text text-muted">Leave blank to keep current password</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="group" class="form-label">Group</label>
                            <select class="form-select" id="group" name="group" required>
                                <option value="client" <?php echo $editUser['group'] === 'client' ? 'selected' : ''; ?>>Client</option>
                                <option value="manager" <?php echo $editUser['group'] === 'manager' ? 'selected' : ''; ?>>Manager</option>
                                <option value="admin" <?php echo $editUser['group'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="enabled" <?php echo $editUser['status'] === 'enabled' ? 'selected' : ''; ?>>Enabled</option>
                                <option value="disabled" <?php echo $editUser['status'] === 'disabled' ? 'selected' : ''; ?>>Disabled</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Update User</button>
                            <a href="/admin/users" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <h6>User Information</h6>
                            <p><strong>Created:</strong> <?php echo date('Y-m-d H:i', strtotime($editUser['created'])); ?></p>
                        </div>
                        
                        <div class="alert alert-info">
                            <h6>User Groups</h6>
                            <ul class="mb-0">
                                <li><strong>Admin:</strong> Full access to all features</li>
                                <li><strong>Manager:</strong> Limited access (if implemented)</li>
                                <li><strong>Client:</strong> Can only view front end, not admin.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </main>
</div>
