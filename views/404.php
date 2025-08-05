<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Page Not Found - <?php echo htmlspecialchars($siteName ?? 'Knowledge Base'); ?></title>
        <link href="/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
        <link href="/css/other.css" rel="stylesheet" type="text/css">
    </head>
    <body class="error-404">
        <div class="container">
            <div class="error-container">
                <div class="error-header">
                    <div class="error-icon">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div class="error-code">404</div>
                    <h1 class="error-title">Page Not Found</h1>
                    <p class="error-subtitle">Sorry, we couldn't find the page you're looking for.</p>
                    <p class="error-description">
                        The page you are trying to access doesn't exist or has been moved. 
                        Please check the URL or return to the knowledge base homepage.
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
