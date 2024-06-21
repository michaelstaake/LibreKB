<?php
    require_once('config.php');
    $setting = new Setting();
    $siteName = $setting->getSettingValue('site_name');
    if ($siteName == '') {
        $siteName = 'Knowledge Base';
    }
    $setting = new Setting();
    $maintenanceMode = $setting->getSettingValue('maintenance_mode');
    if ($maintenanceMode == 'enabled') {
        $setting = new Setting();
        $maintenanceMessage = $setting->getSettingValue('maintenance_message');
        if ($maintenanceMessage == '') {
            $maintenanceMessage = 'The Knowledge Base is undergoing maintenance, please check back later.';
        }
        require_once('header.php');
        echo '<div class="container"><div class="alert alert-primary" role="alert">'.$maintenanceMessage.'</div></div>';
        require_once('footer.php');
        exit;
    } else {
        if (isset($_GET['query']) && $_GET['query'] != '') {
            /* Search Results Page */
            $query = htmlspecialchars($_GET['query'], ENT_QUOTES, 'UTF-8');
            $search = new Search();
            $search->query = $query;
            $results = $search->search();
            $pageTitle = 'Search Results';
            require_once('header.php');
            ?>
                <div class="container">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Knowledge Base</a></li>
                        <li class="breadcrumb-item"><a href="search.php">Search</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo $query; ?></li>
                    </ol>
                    <header>
                        <h1>Search results for "<?php echo $query; ?>"</h1>
                    </header>
                    <main>
                        
                        <?php
                            if (!$results) {
                                echo '<p><i>No results found. <a href="index.php">Return to home page</a> or <a href="search.php">start new search</a>?</p>';
                            } else {
                                foreach($results as $result) {
                                    echo '
                                    <div class="article-item">
                                        <a href="index.php?page=article&a=' . $result['slug'] . '">
                                            <div>
                                                <h6><i class="bi bi-file-earmark"></i>  ' . $result['title'] . '</h6>
                                            </div>
                                        </a>
                                    </div>
                                    ';
                                }
                            }
                        ?>
                    </main>
                </div>
                
            <?php
            require_once('footer.php');
        } else {
            /* Search Page */
            $pageTitle = 'Search';
            require_once('header.php');
            ?>
                <div class="container">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Knowledge Base</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Search</li>
                    </ol>
                    <header>
                        <h1><?php echo $pageTitle; ?></h1>
                    </header>
                    <main>
                        <p>Search for relevant articles in the knowledge base.</p>
                        <form action="search.php" method="GET">
                            <div class="mb-3">
                                <input type="text" class="form-control" name="query" placeholder="Search" style="max-width:400px;">
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Search</button>
                        </form>
                    </main>
                </div>
                
            <?php
            require_once('footer.php');
        }
            
    }

?>