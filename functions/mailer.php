<?php
// Alustaa ja konfiguroi PHPMailer-sähköpostipalvelun
require_once __DIR__ . "/../config/mail.php";
require_once __DIR__ . "/../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function getMailer()
{
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->SMTPAuth = true;

    // SMTP-palvelimen asetukset konfiguraatiosta
    $mail->Host = SMTP_HOST;
    $mail->Port = SMTP_PORT;
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASS;
    $mail->SMTPSecure = (defined('SMTP_SECURE') && SMTP_SECURE === 'ssl') 
        ? PHPMailer::ENCRYPTION_SMTPS 
        : PHPMailer::ENCRYPTION_STARTTLS;

    $mail->isHTML(true);
    $mail->CharSet = "UTF-8";

    return $mail;
}
