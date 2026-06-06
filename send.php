<?php
include 'config.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

function redirectWithAlert($message)
{
    echo '<script>';
    echo 'alert(' . json_encode($message) . ');';
    echo "document.location.href = 'index.php';";
    echo '</script>';
}

function parseRecipients($value)
{
    $parts = preg_split('/[\r\n,]+/', $value);
    $recipients = [];

    foreach ($parts as $part) {
        $email = trim($part);

        if ($email === '') {
            continue;
        }

        $recipients[] = $email;
    }

    return array_values(array_unique($recipients));
}

function invalidRecipients($recipients)
{
    $invalid = [];

    foreach ($recipients as $recipient) {
        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            $invalid[] = $recipient;
        }
    }

    return $invalid;
}

if (isset($_POST['send'])) {
    $recipients = parseRecipients($_POST['recipients'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (count($recipients) === 0 || $subject === '' || $message === '') {
        redirectWithAlert('Recipients, subject, and message are required.');
        exit;
    }

    $invalid = invalidRecipients($recipients);

    if (count($invalid) > 0) {
        redirectWithAlert('Invalid recipient email: ' . implode(', ', $invalid));
        exit;
    }

    $smtpHost = requiredEnv('SMTP_HOST');
    $smtpPort = (int) requiredEnv('SMTP_PORT');
    $smtpSecure = requiredEnv('SMTP_SECURE');
    $smtpUsername = requiredEnv('SMTP_USERNAME');
    $smtpPassword = requiredEnv('SMTP_PASSWORD');
    $smtpFrom = requiredEnv('SMTP_FROM');

    if (
        $smtpHost === '' ||
        $smtpPort <= 0 ||
        $smtpUsername === '' ||
        $smtpPassword === '' ||
        !filter_var($smtpFrom, FILTER_VALIDATE_EMAIL)
    ) {
        redirectWithAlert('Unable to send email. Complete your SMTP configuration first.');
        exit;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = $smtpHost;
        $mail->SMTPAuth = true;
        $mail->Username = $smtpUsername;
        $mail->Password = $smtpPassword;
        $mail->SMTPSecure = $smtpSecure;
        $mail->Port = $smtpPort;
        $mail->setFrom($smtpFrom);

        foreach ($recipients as $recipient) {
            $mail->addAddress($recipient);
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;

        $mail->send();
        redirectWithAlert('Sent successfully!');
    } catch (Exception $exception) {
        redirectWithAlert('Unable to send email right now. Check your SMTP configuration.');
    }
}
?>
