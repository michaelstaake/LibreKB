<div class="container">
    
    <main>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="bi bi-person-circle"></i> Profile Settings</h4>
                    </div>
                    <div class="card-body">
                        
                        <p class="text-muted">Welcome back, <?php echo htmlspecialchars($user['name'] ?? $user['email']); ?>!</p>
                        <p class="text-muted"><?php echo ucfirst($user['group']); ?></p>
                        <form action="<?php echo $basePath; ?>/profile" method="POST">
                            <?php if (in_array($user['group'], ['admin', 'manager'])): ?>
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                </div>
                            <?php endif; ?>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Update Profile</button>
                                <a href="<?php echo $basePath; ?>/" class="btn btn-secondary">Back to Knowledge Base</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>                  
    </main>
</div>
