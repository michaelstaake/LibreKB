<?php
require 'vendor/phpmailer/src/Exception.php';
require 'vendor/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/src/SMTP.php';
class Config {
    public function __construct() {
        /* Database Configuration */
        $this->db_host = 'localhost';
        $this->db_user = 'X';
        $this->db_pass = 'X';
        $this->db_name = 'X';
        /* System Configuration */
        $this->systemURL = 'https://X/'; //example https://kb.example.com/ or https://example.com/kb/
        $this->updateCheck = 'yes'; //change this to no if you wish to disable the update check. 
        /* Email Configuration */
        $this->mailHost       = 'X';                     //Set the SMTP server to send through
        $this->mailSMTPAuth   = true;                    //Enable SMTP authentication
        $this->mailUsername   = 'X';                     //SMTP username
        $this->mailPassword   = 'X';                     //SMTP password
        $this->mailSMTPSecure = 'tls';                   //Enable implicit TLS encryption
        $this->mailPort       = 587;                     //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        $this->mailFrom = 'noreply@X';
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