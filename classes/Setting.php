<?php

class Setting extends Database {
    public function getSettingValue($settingName) {
        $query = "SELECT value FROM settings WHERE name = :name";
        $stmt = $this->connect()->prepare($query);
        $stmt->bindParam(':name', $settingName);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['value'];
    }

    public function updateSettingValue($settingName, $settingValue) {
        $query = "UPDATE settings SET value = :value WHERE name = :name";
        $stmt = $this->connect()->prepare($query);
        $stmt->bindParam(':name', $settingName);
        $stmt->bindParam(':value', $settingValue);
        $stmt->execute();
    }
}

?>