<?php

require_once __DIR__ . '/../src/EnvLoader.php';
require_once __DIR__ . '/../src/MailerConfig.php';
require_once __DIR__ . '/../src/Mailer.php';

$config = MailerConfig::fromEnv(__DIR__ . '/../.env');
$mailer = new Mailer($config);

$result = $mailer->sendTo(
    'recipient@example.com',
    'Welcome message',
    '<p>Hello, this is a target-send message.</p>',
    [
        'from_name' => 'Mailer Module',
    ]
);

print_r($result);
