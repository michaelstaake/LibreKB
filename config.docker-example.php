<?php
require 'vendor/phpmailer/src/Exception.php';
require 'vendor/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/src/SMTP.php';

class Config {
    public function __construct() {
        /* Database Configuration */
        $this->db_host = 'db'; // Must match the service name in docker-compose
        $this->db_user = getenv('MYSQL_USER'); // Must match the values in .env file
        $this->db_pass = getenv('MYSQL_PASSWORD');
        $this->db_name = getenv('MYSQL_DATABASE');

        /* System Configuration */
        $this->systemURL = 'http://localhost/'; // Change to your actual public or private IP or domain. example http://3.109.3.174/ or http://192.168.1.44/ or http://example.com/
        $this->updateCheck = 'no'; // change this to yes if you wish to enable the update check.

        /* Email Configuration */
        $this->mailHost       = 'smtp.example.com'; // Replace with your SMTP server
        $this->mailSMTPAuth   = true;               
        $this->mailUsername   = 'your-email@example.com';
        $this->mailPassword   = 'your-email-password';
        $this->mailSMTPSecure = 'tls';
        $this->mailPort       = 587;
        $this->mailFrom       = 'noreply@example.com';
    }
}

require_once('classes/Database.php');
require_once('classes/Search.php');
require_once('classes/Email.php');
require_once('classes/Article.php');
require_once('classes/Category.php');
require_once('classes/Setting.php');
require_once('classes/User.php');
?>
