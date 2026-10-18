<?php
class Version {
    public $version;
    public $channel;

    public function __construct() {
        $this->version = '2.1.0';
        $this->channel = 'release';
    }
}
?>