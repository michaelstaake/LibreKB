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

        $data = [
            'pageTitle' => 'LibreKB Upgrade',
            'message' => $this->getMessage(),
            'error' => $this->getError(),
            'coreMissing' => $coreMissing,
            'needsAttachments' => $needsAttachments,
            'missingSettings' => $missingSettings,
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
            $database->ensureApiSettings();
            $this->setMessage('Upgrade complete. The attachments table is present and the read-only API settings were added. Existing categories, articles, users, and logs were left unchanged. The API stays disabled until you enable it in Settings.');
            return $this->redirect('/upgrade');
        } catch (Exception $e) {
            $this->setError('Upgrade failed: ' . $e->getMessage());
            return $this->redirect('/upgrade');
        }
    }
}
