<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo($pageTitle); ?> - <?php echo($siteName); ?></title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
        <link href="css/kb.css" rel="stylesheet" type="text/css">
        <?php
            $setting = new Setting();
            $siteColor = $setting->getSettingValue('site_color');
            if (!$siteColor == '') {
                echo '<style>nav { background-color: '.$siteColor.'; }</style>';
            }
        ?>
    </head>
    <body>
        <nav>
            <div class="container nav-container">
                <div class="logo">
                    <?php
                        $setting = new Setting();
                        $siteLogo = $setting->getSettingValue('site_logo');
                        if (!$siteLogo == '') {
                            echo '<a href="index.php"><img class="kb-logo" src="'.$siteLogo.'" alt="'.$siteName.' Logo"/></a>';
                        } else {
                            echo '<a href="index.php">'.$siteName.'</a>';
                        }
                    ?>
                </div>
                <ul>
                    <li><a href="index.php"><i class="bi bi-house-door"></i> Knowledge Base</a></li>
                    <!--<li><a href="index.php?page=search" ><i class="bi bi-search"></i> Search</a></li>-->
                </ul>
            </div>
        </nav>
        