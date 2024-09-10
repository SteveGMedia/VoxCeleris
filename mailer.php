<?php
/*
    mailer.php
    ----------

    This file contains a function to send an email using PHPMailer. I used composer to install PHPMailer
    and autoload it in this file.

    The built-in PHP mail() function would not play nice with my dev email server (SMTP4Dev), so I used
    PHPMailer instead after frustratingly trying to get mail() to work for about 2 hours.
    
    
    Author: SteveGMedia
*/

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once 'vendor/autoload.php';
require_once 'config.php';

function sendEmail($to, $subject, $body, $altBody = '') {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = SMTP_AUTH;
        $mail->Username   = EMAIL_FROM;
        $mail->Password   = EMAIL_PASS;
        $mail->Port       = SMTP_PORT;

        $mail->setFrom(EMAIL_FROM, EMAIL_NAME);
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $altBody;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>