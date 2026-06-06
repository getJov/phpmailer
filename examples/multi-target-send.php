<?php

require_once __DIR__ . '/../src/EnvLoader.php';
require_once __DIR__ . '/../src/MailerConfig.php';
require_once __DIR__ . '/../src/Mailer.php';

$config = MailerConfig::fromEnv(__DIR__ . '/../.env');
$mailer = new Mailer($config);

$result = $mailer->sendToMany(
    [
        'first-recipient@example.com',
        'second-recipient@example.com',
    ],
    'Plain text update',
    "Hello,\n\nThis message is sent in plain-text mode.",
    [
        'is_html' => false,
    ]
);

print_r($result);
