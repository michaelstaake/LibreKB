<?php
class Version {
    public $version;
    public $channel;

    public function __construct() {
        $this->version = '2.3.1';
        $this->channel = 'release';
    }
}
?>