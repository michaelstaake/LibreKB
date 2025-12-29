<div class="container">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo $basePath; ?>/">Knowledge Base</a></li>
        <li class="breadcrumb-item active" aria-current="page">Search</li>
    </ol>
    
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <header class="text-center mb-5">
                <div class="mb-4">
                    <i class="bi bi-search display-1 text-primary"></i>
                </div>
                <h1 class="mb-3">Search Knowledge Base</h1>
                <p class="lead text-muted">Find articles and categories quickly</p>
            </header>
            
            <main>
                <form action="<?php echo $basePath; ?>/search" method="POST">
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input type="text" 
                                       class="form-control border-start-0 ps-0" 
                                       id="query" 
                                       name="query" 
                                       placeholder="Enter your search terms..." 
                                       required
                                       autocomplete="off">
                                <button type="submit" class="btn btn-primary px-4">
                                    Search
                                </button>
                            </div>
                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Search through articles and categories to find what you're looking for
                                </small>
                            </div>
                        </div>
                    </div>
                </form>
                
                <div class="mt-4 text-center">
                    <small class="text-muted">
                        <i class="bi bi-lightbulb me-1"></i>
                        <strong>Tip:</strong> Try searching for keywords, topics, or browse by 
                        <a href="<?php echo $basePath; ?>/" class="text-decoration-none">categories</a>
                    </small>
                </div>
            </main>
        </div>
    </div>
</div>
