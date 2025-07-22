<div class="container">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">Knowledge Base</a></li>
        <li class="breadcrumb-item active" aria-current="page">Search</li>
    </ol>
    
    <header>
        <h1>Search</h1>
    </header>
    
    <main>
        <form action="/search" method="POST">
            <div class="mb-3">
                <label for="query" class="form-label">Search Query</label>
                <input type="text" class="form-control" id="query" name="query" required>
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </main>
</div>
