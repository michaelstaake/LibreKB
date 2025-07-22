<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo htmlspecialchars($pageTitle); ?></title>
        <link href="/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
        <link href="/css/other.css" rel="stylesheet" type="text/css">
    </head>
    <body class="login-page">
        <div class="container">
            <div class="auth-container">
                <div class="auth-header">
                    <div class="auth-icon">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <h1 class="auth-title">Sign In</h1>
                    <p class="auth-subtitle">Welcome to <?php echo htmlspecialchars($siteName ?? 'Knowledge Base'); ?>! Please enter your credentials.</p>
                </div>
                
                <?php if (isset($error) && !empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($message) && !empty($message)): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>
                
                <form action="/login" method="POST" novalidate>
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="bi bi-envelope me-2"></i>Email Address
                        </label>
                        <input type="email" class="form-control" id="email" name="email" required autofocus>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock me-2"></i>Password
                        </label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                        </button>
                    </div>
                </form>
                
                <div class="text-center">
                    <a href="/password/reset" class="auth-link">
                        <i class="bi bi-key me-1"></i>Forgot your password?
                    </a>
                    <br>
                    <a href="/" class="auth-link mt-2 d-inline-block">
                        <i class="bi bi-house me-1"></i>Back to <?php echo htmlspecialchars($siteName ?? 'Knowledge Base'); ?>
                    </a>
                </div>
            </div>
        </div>
        
        <script src="/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
