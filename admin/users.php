<?php

    session_start();
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    require_once('../config.php');

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $siteName = $_POST['site_name'];
        $siteColor = $_POST['site_color'];
        $siteLogo = $_POST['site_logo'];
        $maintenanceMode = $_POST['maintenance_mode'];
        $maintenanceMessage = $_POST['maintenance_message'];

        $setting = new Setting();
        $setting->updateSettingValue('site_name', $siteName);
        $setting->updateSettingValue('site_color', $siteColor);
        $setting->updateSettingValue('site_logo', $siteLogo);
        $setting->updateSettingValue('maintenance_mode', $maintenanceMode);
        $setting->updateSettingValue('maintenance_message', $maintenanceMessage);

        header('Location: settings.php?msg=saved');
        exit;
    }

    $pageCategory = "Users";
    $pageTitle = "Users";

    require_once('header.php');
?>

<div class="container">
    <?php
        if (isset($_GET['msg']) && $_GET['msg'] == 'saved') {
            echo '<div class="alert alert-success" role="alert">Settings saved</div>';
        }
    ?>
    <header>
        <h1>Users</h1>
    </header>
    <main>
        <p>coming soon</p>
    </main>
</div>

<?php
    require_once('footer.php');
?>