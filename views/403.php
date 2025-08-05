<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Access Forbidden - <?php echo htmlspecialchars($siteName ?? 'Knowledge Base'); ?></title>
        <link href="/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
        <link href="/css/other.css" rel="stylesheet" type="text/css">
    </head>
    <body class="error-403">
        <div class="container">
            <div class="error-container">
                <div class="error-header">
                    <div class="error-icon">
                        <i class="bi bi-lock"></i>
                    </div>
                    <div class="error-code">403</div>
                    <h1 class="error-title">Access Forbidden</h1>
                    <p class="error-subtitle">You don't have permission to access this resource.</p>
                    <p class="error-description">
                        This page or action requires higher privileges than your current account has. 
                        Please contact an administrator if you believe you should have access to this content.
                    </p>
                </div>
                
                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                    <a href="/" class="btn btn-primary">
                        <i class="bi bi-house-door me-2"></i>Go Home
                    </a>
                    <a href="javascript:history.back()" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Go Back
                    </a>
                </div>
            </div>
        </div>
        
        <script src="/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
