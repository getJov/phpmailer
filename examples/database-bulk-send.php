<?php

require_once __DIR__ . '/../src/EnvLoader.php';
require_once __DIR__ . '/../src/MailerConfig.php';
require_once __DIR__ . '/../src/Mailer.php';

$config = MailerConfig::fromEnv(__DIR__ . '/../.env');
$mailer = new Mailer($config);

mysqli_report(MYSQLI_REPORT_OFF);

$conn = @mysqli_connect('localhost', 'root', '', 'your_database');

if (!$conn) {
    print_r([
        'success' => false,
        'sent' => 0,
        'failed' => 0,
        'errors' => ['Unable to connect to the database.'],
    ]);

    return;
}

$result = $mailer->sendBulkFromDatabase(
    $conn,
    'SELECT email FROM emails',
    'Bulk announcement',
    '<p>Hello, this message is sent to every email returned by the query.</p>'
);

print_r($result);
