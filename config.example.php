<?php
require 'vendor/phpmailer/src/Exception.php';
require 'vendor/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/src/SMTP.php';
require 'version.php';
class Config {
    // Database Configuration
    public $db_host;
    public $db_user;
    public $db_pass;
    public $db_name;
    
    // System Configuration
    public $systemURL;
    public $updateCheck;
    
    // Email Configuration
    public $mailHost;
    public $mailSMTPAuth;
    public $mailUsername;
    public $mailPassword;
    public $mailSMTPSecure;
    public $mailPort;
    public $mailFrom;
    
    public function __construct() {
        /* Database Configuration */
        $this->db_host = 'localhost';
        $this->db_user = 'X';
        $this->db_pass = 'X';
        $this->db_name = 'X';

        /* System Configuration */
        $this->systemURL = 'https://X.X.X/'; //example https://kb.example.com/ or https://example.com/kb/
        $this->updateCheck = 'yes'; //Acceptable values are yes or no. Recommended value is yes

        /* Email Configuration */
        $this->mailHost       = 'X';                     //Set the SMTP server to send through
        $this->mailSMTPAuth   = true;                    //Enable SMTP authentication
        $this->mailUsername   = 'X@X.X';                     //SMTP username
        $this->mailPassword   = 'X';                     //SMTP password
        $this->mailSMTPSecure = 'tls';                   //Enable implicit TLS encryption
        $this->mailPort       = 587;                     //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        $this->mailFrom = 'X';
        
        // Configure error reporting based on channel setting
        $this->configureErrorReporting();
    }
    
    private function configureErrorReporting() {
        $version = new Version();
        
        if ($version->channel === 'beta') {
            // Show all errors for beta channel
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
        } else {
            // Hide errors for release channel
            error_reporting(0);
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);
        }
    }
}
require_once('models/Database.php');
require_once('models/Search.php');
require_once('models/Email.php');
require_once('models/Article.php');
require_once('models/Category.php');
require_once('models/Setting.php');
require_once('models/User.php');
require_once('models/Log.php');
?>