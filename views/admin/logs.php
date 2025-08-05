<div class="container">
    
    <main>
        <header>
            <div>
                <h1>Activity Log</h1>
                <p class="text-muted">Monitor user activity and system changes</p>
            </div>
            <hr>
        </header>
        
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history"></i> Recent Activity
                            <span class="badge bg-secondary ms-2"><?php echo $totalLogs; ?> total entries</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($logs)): ?>
                            <div class="alert alert-info" role="alert">
                                <i class="bi bi-info-circle"></i> No activity logs found.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date & Time</th>
                                            <th>User</th>
                                            <th>Action</th>
                                            <th>Target</th>
                                            <th>IP Address</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($logs as $log): ?>
                                            <tr>
                                                <td>
                                                    <small class="text-muted">
                                                        <?php echo date('M j, Y g:i A', strtotime($log['when'])); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($log['user_name'] ?? 'Unknown'); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($log['user_email']); ?></small>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars(ucfirst($log['performed_action'])); ?>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars(ucfirst($log['on_what'])); ?>
                                                </td>
                                                <td>
                                                    <small class="text-muted font-monospace">
                                                        <?php echo htmlspecialchars($log['user_ip']); ?>
                                                    </small>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <?php if ($totalPages > 1): ?>
                                <nav aria-label="Log pagination">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($currentPage > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="/admin/logs?page=<?php echo $currentPage - 1; ?>">Previous</a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php
                                        $startPage = max(1, $currentPage - 2);
                                        $endPage = min($totalPages, $currentPage + 2);
                                        
                                        for ($i = $startPage; $i <= $endPage; $i++):
                                        ?>
                                            <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                                                <a class="page-link" href="/admin/logs?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($currentPage < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="/admin/logs?page=<?php echo $currentPage + 1; ?>">Next</a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
