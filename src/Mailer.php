<?php

require_once __DIR__ . '/MailerConfig.php';

use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;

class Mailer
{
    private $config;
    private $phpMailerSrcPath;
    private $runtimeLoaded = false;
    private $runtimeErrors = [];

    public function __construct(MailerConfig $config, ?string $phpMailerSrcPath = null)
    {
        $this->config = $config;
        $this->phpMailerSrcPath = $phpMailerSrcPath === null
            ? dirname(__DIR__) . '/phpmailer/src'
            : rtrim($phpMailerSrcPath, '/\\');
    }

    public function sendTo(string $email, string $subject, string $message, array $options = []): array
    {
        return $this->sendToMany([$email], $subject, $message, $options);
    }

    public function sendToMany(array $emails, string $subject, string $message, array $options = []): array
    {
        $recipients = $this->normalizeRecipients($emails);

        if (count($recipients) === 0) {
            return $this->result(false, 0, 0, ['At least one recipient email is required.']);
        }

        if (trim($subject) === '') {
            return $this->result(false, 0, count($recipients), ['Subject is required.']);
        }

        if (trim($message) === '') {
            return $this->result(false, 0, count($recipients), ['Message is required.']);
        }

        if ($this->hasInvalidRecipients($recipients)) {
            return $this->result(false, 0, count($recipients), ['One or more recipient emails are invalid.']);
        }

        $optionResult = $this->normalizeOptions($options);

        if (count($optionResult['errors']) > 0) {
            return $this->result(false, 0, count($recipients), $optionResult['errors']);
        }

        $configErrors = $this->config->validate();

        if (count($configErrors) > 0) {
            return $this->result(false, 0, count($recipients), $configErrors);
        }

        $runtimeErrors = $this->loadPhpMailerRuntime();

        if (count($runtimeErrors) > 0) {
            return $this->result(false, 0, count($recipients), $runtimeErrors);
        }

        $sent = 0;
        $failed = 0;
        $errors = [];

        foreach ($recipients as $recipient) {
            try {
                $mail = $this->buildMail($optionResult['from_name'], $optionResult['is_html']);
                $mail->addAddress($recipient);
                $mail->Subject = $subject;
                $mail->Body = $message;

                if ($optionResult['is_html']) {
                    $mail->AltBody = trim(strip_tags($message));
                }

                $mail->send();
                $sent++;
            } catch (PHPMailerException $exception) {
                $failed++;
                $errors[] = 'Unable to send to one recipient. Check SMTP configuration.';
            } catch (Throwable $exception) {
                $failed++;
                $errors[] = 'Unable to send to one recipient right now.';
            }
        }

        return $this->result($failed === 0, $sent, $failed, $errors);
    }

    public function sendBulkFromDatabase(
        mysqli $conn,
        string $query,
        string $subject,
        string $message,
        array $options = []
    ): array {
        try {
            $result = $conn->query($query);
        } catch (Throwable $exception) {
            return $this->result(false, 0, 0, ['Unable to load recipients from database.']);
        }

        if (!$result instanceof mysqli_result) {
            return $this->result(false, 0, 0, ['Unable to load recipients from database.']);
        }

        if (!$this->resultHasEmailColumn($result)) {
            $result->free();

            return $this->result(false, 0, 0, ['Recipient query must return an email column.']);
        }

        $emails = [];

        while ($row = $result->fetch_assoc()) {
            $email = trim((string) ($row['email'] ?? ''));

            if ($email !== '') {
                $emails[] = $email;
            }
        }

        $result->free();

        if (count($emails) === 0) {
            return $this->result(false, 0, 0, ['No valid recipients found.']);
        }

        return $this->sendToMany($emails, $subject, $message, $options);
    }

    private function buildMail(string $fromName, bool $isHtml): PHPMailer
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $this->config->getHost();
        $mail->SMTPAuth = true;
        $mail->Username = $this->config->getUsername();
        $mail->Password = $this->config->getPassword();
        $mail->SMTPSecure = $this->config->getSecure();
        $mail->Port = $this->config->getPort();
        $mail->setFrom($this->config->getFrom(), $fromName);
        $mail->isHTML($isHtml);

        return $mail;
    }

    private function loadPhpMailerRuntime(): array
    {
        if ($this->runtimeLoaded || count($this->runtimeErrors) > 0) {
            return $this->runtimeErrors;
        }

        $requiredFiles = [
            $this->phpMailerSrcPath . '/Exception.php',
            $this->phpMailerSrcPath . '/PHPMailer.php',
            $this->phpMailerSrcPath . '/SMTP.php',
        ];

        foreach ($requiredFiles as $file) {
            if (!is_file($file)) {
                $this->runtimeErrors[] = 'PHPMailer runtime files are missing.';

                return $this->runtimeErrors;
            }
        }

        foreach ($requiredFiles as $file) {
            require_once $file;
        }

        if (!class_exists(PHPMailer::class)) {
            $this->runtimeErrors[] = 'PHPMailer runtime did not load.';

            return $this->runtimeErrors;
        }

        $this->runtimeLoaded = true;

        return [];
    }

    private function normalizeRecipients(array $emails): array
    {
        $recipients = [];

        foreach ($emails as $email) {
            if (!is_scalar($email)) {
                $recipients[] = '';
                continue;
            }

            $email = trim((string) $email);

            if ($email === '') {
                continue;
            }

            $recipients[] = $email;
        }

        return array_values(array_unique($recipients));
    }

    private function hasInvalidRecipients(array $recipients): bool
    {
        foreach ($recipients as $recipient) {
            if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                return true;
            }
        }

        return false;
    }

    private function normalizeOptions(array $options): array
    {
        $errors = [];
        $isHtml = true;

        if (array_key_exists('is_html', $options)) {
            if (!is_bool($options['is_html'])) {
                $errors[] = 'is_html option must be a boolean.';
            } else {
                $isHtml = $options['is_html'];
            }
        }

        $fromName = $this->config->getFromName();

        if (array_key_exists('from_name', $options)) {
            if (!is_string($options['from_name'])) {
                $errors[] = 'from_name option must be a string.';
            } else {
                $fromName = trim($options['from_name']);
            }
        }

        if (MailerConfig::containsLineBreak($fromName)) {
            $errors[] = 'Sender display name cannot contain line breaks.';
        }

        return [
            'errors' => $errors,
            'from_name' => $fromName,
            'is_html' => $isHtml,
        ];
    }

    private function result(bool $success, int $sent, int $failed, array $errors): array
    {
        return [
            'success' => $success,
            'sent' => $sent,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    private function resultHasEmailColumn(mysqli_result $result): bool
    {
        foreach ($result->fetch_fields() as $field) {
            if ($field->name === 'email') {
                return true;
            }
        }

        return false;
    }
}
