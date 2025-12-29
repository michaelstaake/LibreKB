<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo htmlspecialchars($pageTitle); ?></title>
        <link href="<?php echo $basePath; ?>/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
        <link href="<?php echo $basePath; ?>/css/other.css" rel="stylesheet" type="text/css">
    </head>
    <body class="password-new-page">
        <div class="container">
            <div class="auth-container">
                <div class="auth-header">
                    <div class="auth-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h1 class="auth-title">Set New Password</h1>
                    <p class="auth-subtitle">Choose a strong password to secure your account.</p>
                </div>
                
                <?php if (isset($error) && !empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <form action="<?php echo $basePath; ?>/password/new" method="POST" novalidate>
                    <div class="mb-3">
                        <label for="password1" class="form-label">
                            <i class="bi bi-lock me-2"></i>New Password
                        </label>
                        <input type="password" class="form-control" id="password1" name="password1" 
                               autocomplete="new-password" required autofocus>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password2" class="form-label">
                            <i class="bi bi-lock me-2"></i>Confirm Password
                        </label>
                        <input type="password" class="form-control" id="password2" name="password2" 
                               autocomplete="new-password" required>
                    </div>
                    
                    <div class="password-hint">
                        <i class="bi bi-info-circle me-2"></i>
                        Use at least 8 characters with a mix of letters, numbers, and symbols.
                    </div>
                    
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-2"></i>Update Password
                        </button>
                    </div>
                </form>
                
                <div class="text-center">
                    <a href="<?php echo $basePath; ?>/login" class="auth-link">
                        <i class="bi bi-arrow-left me-1"></i>Back to Sign In
                    </a>
                </div>
            </div>
        </div>
        
        <script src="<?php echo $basePath; ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script>
            // Real-time password matching validation
            document.getElementById('password2').addEventListener('input', function() {
                const password1 = document.getElementById('password1').value;
                const password2 = this.value;
                
                if (password2 && password1 !== password2) {
                    this.setCustomValidity('Passwords do not match');
                } else {
                    this.setCustomValidity('');
                }
            });
            
            document.getElementById('password1').addEventListener('input', function() {
                const password2 = document.getElementById('password2');
                if (password2.value) {
                    password2.dispatchEvent(new Event('input'));
                }
            });
        </script>
    </body>
</html>
