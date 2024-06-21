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
            $pageTitle = 'Search Results';
            require_once('header.php');
            ?>
                <div class="container">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Knowledge Base</a></li>
                        <li class="breadcrumb-item"><a href="index.php?page=search">Search</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo $_GET['query']; ?></li>
                    </ol>
                    <header>
                        <h1><?php echo $pageTitle; ?></h1>
                    </header>
                    <main>
                        <p>Coming soon</p>
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
                        <p>Coming soon</p>
                    </main>
                </div>
                
            <?php
            require_once('footer.php');
        }
            
    }

?>