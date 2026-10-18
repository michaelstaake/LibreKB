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

        $data = [
            'pageTitle' => 'LibreKB Upgrade',
            'message' => $this->getMessage(),
            'error' => $this->getError(),
            'coreMissing' => $coreMissing,
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
            $this->setMessage('Upgrade complete. Existing categories, articles, users, and settings were left unchanged.');
            return $this->redirect('/upgrade');
        } catch (Exception $e) {
            $this->setError('Upgrade failed: ' . $e->getMessage());
            return $this->redirect('/upgrade');
        }
    }
}
