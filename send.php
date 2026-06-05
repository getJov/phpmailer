<?php
include 'connect.php';

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

function requiredEnv($key)
{
    return trim(envValue($key, ''));
}

if (isset($_POST['send'])) {
    if (!$conn) {
        redirectWithAlert('Unable to send email. Check your database configuration.');
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

    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($subject === '' || $message === '') {
        redirectWithAlert('Subject and message are required.');
        exit;
    }

    $result = mysqli_query($conn, 'SELECT email FROM emails');

    if ($result === false) {
        redirectWithAlert('Unable to load recipients right now.');
        exit;
    }

    $mail = new PHPMailer(true);
    $recipientCount = 0;

    try {
        $mail->isSMTP();
        $mail->Host = $smtpHost;
        $mail->SMTPAuth = true;
        $mail->Username = $smtpUsername;
        $mail->Password = $smtpPassword;
        $mail->SMTPSecure = $smtpSecure;
        $mail->Port = $smtpPort;
        $mail->setFrom($smtpFrom);

        while ($row = mysqli_fetch_assoc($result)) {
            $email = trim($row['email'] ?? '');

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $mail->addAddress($email);
            $recipientCount++;
        }

        if ($recipientCount === 0) {
            redirectWithAlert('No valid recipients found.');
            exit;
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
