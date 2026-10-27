<?php

class UpgradeController extends Controller
{
    public function index()
    {
        if ($this->isPost()) {
            return $this->run();
        }

        $database = new Database();
        $coreMissing = $database->checkCoreTables();
        $needsAttachments = empty($coreMissing) && in_array('attachments', $database->checkRequiredTables(), true);
        $missingSettings = empty($coreMissing) ? $database->missingApiSettings() : [];
        $needsSettingsText = empty($coreMissing) && !$database->settingsValueIsText();
        $missingCustomCss = empty($coreMissing) && $database->missingCustomCssSetting();
        $missingEditorToolbar = empty($coreMissing) && $database->missingEditorToolbarSetting();
        $missingGoogleFonts = empty($coreMissing) && $database->missingGoogleFontsSetting();

        $data = [
            'pageTitle' => 'LibreKB Upgrade',
            'message' => $this->getMessage(),
            'error' => $this->getError(),
            'coreMissing' => $coreMissing,
            'needsAttachments' => $needsAttachments,
            'missingSettings' => $missingSettings,
            'needsSettingsText' => $needsSettingsText,
            'missingCustomCss' => $missingCustomCss,
            'missingEditorToolbar' => $missingEditorToolbar,
            'missingGoogleFonts' => $missingGoogleFonts,
            'needsUpgrade' => empty($coreMissing) ? $database->needsUpgrade() : false,
            'upToDate' => empty($coreMissing) && !$database->needsUpgrade()
        ];

        return $this->view('upgrade', $data);
    }

    public function run()
    {
        $database = new Database();
        $coreMissing = $database->checkCoreTables();

        if (!empty($coreMissing)) {
            $this->setError('This database is not a LibreKB 2.x install yet. Run the installer on an empty database instead.');
            return $this->redirect('/upgrade');
        }

        if (!$database->needsUpgrade()) {
            $this->setMessage('This database is already up to date. No changes were made.');
            return $this->redirect('/upgrade');
        }

        try {
            $database->createAttachmentsTable();
            $database->ensureSettingsValueIsText();
            $database->ensureApiSettings();
            $database->ensureCustomCssSetting();
            $database->ensureEditorToolbarSetting();
            $database->ensureGoogleFontsSetting();
            $this->setMessage('Upgrade complete. The settings value column is TEXT. Custom CSS, the editor toolbar, and the Google Fonts CSS URL settings are present, and the read-only API settings are present. Existing categories, articles, users, and logs were left unchanged. The API stays disabled until you enable it in Settings. An empty editor toolbar keeps the built-in buttons. An empty Google Fonts CSS URL does not load a font.');
            return $this->redirect('/upgrade');
        } catch (Exception $e) {
            $this->setError('Upgrade failed: ' . $e->getMessage());
            return $this->redirect('/upgrade');
        }
    }
}
