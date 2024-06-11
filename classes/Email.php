<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Email extends Config {

    public function sendPasswordReset($email, $token) {

        $mail = new PHPMailer(true);
        try {
            $mail->SMTPDebug = 0;
            $mail->isSMTP();   
            $mail->Host       = $this->mailHost;
            $mail->SMTPAuth   = $this->mailSMTPAuth;
            $mail->Username   = $this->mailUsername;
            $mail->Password   = $this->mailPassword;
            $mail->SMTPSecure = $this->mailSMTPSecure;
            $mail->Port       = $this->mailPort;
            $mail->setFrom($this->mailFrom, $this->mailFrom);

            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'LibreKB Password Reset';
            $mail->Body    = 'To complete the password reset process, please go to the following link: <a href="' . $this->systemURL . 'admin/reset.php?action=setnew&token=' . $token . '">' . $this->systemURL . 'admin/reset.php?action=setnew&token=' . $token . '</a>.';
            $mail->AltBody = 'To complete the password reset process, please go to the following link: ' . $this->systemURL . 'reset.php?action=setnew&token=' . $token . '</a>.';

            $mail->send();
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}

?>