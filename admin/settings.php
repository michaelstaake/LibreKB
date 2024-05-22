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

    $pageCategory = "Settings";
    $pageTitle = "Settings";

    require_once('header.php');
?>

<div class="container">
    <?php
        if (isset($_GET['msg']) && $_GET['msg'] == 'saved') {
            echo '<div class="alert alert-success" role="alert">Settings saved</div>';
        }
    ?>
    <header>
        <h1>Settings</h1>
    </header>
    <main>
        <form action="settings.php" method="POST">
            <div class="settings-section">
                <h5>Branding and Customization</h5>
                <div class="row">
                    <div class="col">
                        <div class="mb-3">
                            <label for="site_name" class="form-label">Site Name</label>
                            <input type="text" class="form-control" id="site_name" name="site_name" value="<?php
                                $setting = new Setting();
                                $siteName = $setting->getSettingValue('site_name');
                                echo $siteName;
                            ?>">
                            <div id="siteNameHelp" class="form-text form-help">If this is blank, the default name "Knowledge Base" will be used.</div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="mb-3">
                            <label for="site_color" class="form-label">Site Color</label>
                            <input type="text" class="form-control" id="site_color" name="site_color" value="<?php
                                $setting = new Setting();
                                $siteColor = $setting->getSettingValue('site_color');
                                echo $siteColor;
                            ?>">
                            <div id="siteColorHelp" class="form-text form-help">If this is blank, the default color of #1B1F22 will be used.</div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="mb-3">
                            <label for="site_logo" class="form-label">Site Logo</label>
                            <input type="text" class="form-control" id="site_logo" name="site_logo" value="<?php
                                $setting = new Setting();
                                $siteLogo = $setting->getSettingValue('site_logo');
                                echo $siteLogo;
                            ?>">
                            <div id="siteLogoHelp" class="form-text form-help">If this is blank, the site title will be displayed in the header.</div>
                            
                        </div>
                    </div>
                </div>
            </div>
            <div class="settings-section">
                <h5>Maintenance Mode</h5>
                <div class="row">
                    <div class="col">
                        <div class="mb-3">
                            <label for="maintenance_mode" class="form-label">Maintenance Mode</label>
                            <?php
                                $setting = new Setting();
                                $maintenanceMode = $setting->getSettingValue('maintenance_mode');
                            ?>
                            <select class="form-select" id="maintenance_mode" name="maintenance_mode">
                                <option value="disabled" <?php if ($maintenanceMode == 'disabled') echo "selected"; ?>>Disabled</option>
                                <option value="enabled" <?php if ($maintenanceMode == 'enabled') echo "selected"; ?>>Enabled</option>
                            </select>
                            <div id="maintenanceModeHelp" class="form-text form-help">If this is enabled, the front end of the site will be disabled and the maintenance message will be displayed.</div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="mb-3">
                            <label for="maintenance_message" class="form-label">Maintenance Message</label>
                            <input type="text" class="form-control" id="maintenance_message" name="maintenance_message" value="<?php
                                $setting = new Setting();
                                $maintenanceMessage = $setting->getSettingValue('maintenance_message');
                                echo $maintenanceMessage;
                            ?>">
                            <div id="maintenanceMessageHelp" class="form-text form-help">If this is blank, and maintenance mode is enabled, the default maintenance message will be displayed: "The Knowledge Base is undergoing maintenance, please check back later."</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="settings-section">
                <button type="submit" class="btn btn-light">Save</button>
            </div>           
        </form>
    </main>
</div>

<?php
    require_once('footer.php');
?>