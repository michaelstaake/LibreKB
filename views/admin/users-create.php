<div class="container">
    
    <main>
        <?php if ($user['group'] !== 'admin'): ?>
            <div class="alert alert-danger" role="alert">You do not have permission to access this page.</div>
        <?php else: ?>
            <header>
                <div>
                    <h1>Create User</h1>
                </div>
                <hr>
            </header>
            <form action="/admin/users/create" method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="group" class="form-label">Group</label>
                            <select class="form-select" id="group" name="group" required>
                                <option value="client">Client</option>
                                <option value="manager">Manager</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="enabled">Enabled</option>
                                <option value="disabled">Disabled</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Create User</button>
                            <a href="/admin/users" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <h6>User Groups</h6>
                            <ul class="mb-0">
                                <li><strong>Admin:</strong> Full access to everything</li>
                                <li><strong>Manager:</strong> Can access admin panel but can only manage articles and categories.</li>
                                <li><strong>Client:</strong> Can only view front end, not admin.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </main>
</div>
