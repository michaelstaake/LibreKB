<div class="container">
    
    <header class="d-flex justify-content-between align-items-center">
        <h1>Users</h1>
        <?php if ($user['group'] === 'admin'): ?>
            <a href="/admin/users/create" class="btn btn-primary">Create User</a>
        <?php endif; ?>
    </header>
    
    <main>
        <?php if ($user['group'] !== 'admin'): ?>
            <div class="alert alert-danger" role="alert">You do not have permission to access this page.</div>
        <?php elseif (empty($users)): ?>
            <p><i>No other users found.</i></p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Group</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $userItem): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($userItem['name'] ?: 'Not set'); ?></td>
                                <td><?php echo htmlspecialchars($userItem['email']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $userItem['group'] === 'admin' ? 'danger' : 'primary'; ?>">
                                        <?php echo ucfirst($userItem['group']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $userItem['status'] === 'enabled' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($userItem['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('Y-m-d H:i', strtotime($userItem['created'])); ?></td>
                                <td>
                                    <a href="/admin/users/<?php echo $userItem['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <a href="/admin/users/<?php echo $userItem['id']; ?>/delete" 
                                       class="btn btn-sm btn-outline-danger" 
                                       onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
</div>
