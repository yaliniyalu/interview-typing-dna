<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/api/PHPMailer/Exception.php';
require_once __DIR__ . '/api/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/api/PHPMailer/SMTP.php';


/**
 * @param $to
 * @param $subject
 * @param $body
 * @param string $alt_body
 * @throws Exception
 */
function sendMail($to, $subject, $body, $alt_body = '') {
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;

    //Recipients
    $mail->setFrom(SEND_MAIL_FROM['address'], SEND_MAIL_FROM['name'] ?? null);
    $mail->addAddress($to['email'], $to['name']);

    // Content
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $body;
    $mail->AltBody = $alt_body;

    $mail->send();
}