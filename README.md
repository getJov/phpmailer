# Mailer Module

This is the `module-version` branch. It is the maintained copy-paste PHP mailer module for adding SMTP email sending to an existing PHP website.

> Recommended branch: `module-version`
>
> Use this branch for new integrations and future maintenance. The `main` and `target-send` branches are reference starter versions only.

The module supports:

- Target send to one recipient
- Target send to multiple recipients
- Database-backed bulk send from any query that returns an `email` column
- HTML or plain-text message mode
- Default or per-send sender display name

The root app pages from the starter branches are intentionally not included on this branch.

## Branch Guide

| Branch | Purpose | Maintenance status |
|--------|---------|--------------------|
| `module-version` | Reusable copy-paste PHP mailer module with target send, multi-recipient send, and database-backed bulk send | Maintained |
| `main` | Original bulk-send starter app | Reference starter only |
| `target-send` | Direct target-send starter app | Reference starter only |

For new projects, use `module-version`. The other branches are useful as starting references, but developers who use them should maintain and extend those branches themselves.

## Requirements

- PHP with the `mysqli` extension if you use database-backed bulk send
- A PHP-capable web server or PHP CLI runtime
- SMTP credentials
- The included local PHPMailer runtime files

No Composer package, framework, JavaScript build step, Laravel, Symfony, or WordPress integration is required.

## Files To Copy

Copy this module folder into your existing PHP project, or clone/download this branch and copy the needed files.

Required files:

```text
mailer/
  src/
    EnvLoader.php
    MailerConfig.php
    Mailer.php
  phpmailer/
    LICENSE
    src/
      Exception.php
      PHPMailer.php
      SMTP.php
  .env.example
```

Optional files:

```text
mailer/
  examples/
  db/mailer.sql
```

Example project layout:

```text
your-site/
  public/
    send-contact.php
  mailer/
    src/
    phpmailer/
    .env
```

## Setup

1. Copy `.env.example` to `.env`.

   ```bash
   cp .env.example .env
   ```

2. Fill in your SMTP settings.

   ```env
   SMTP_HOST=smtp.gmail.com
   SMTP_PORT=465
   SMTP_SECURE=ssl
   SMTP_USERNAME=sender@example.com
   SMTP_PASSWORD=
   SMTP_FROM=sender@example.com
   SMTP_FROM_NAME=Your Website
   ```

Required keys:

- `SMTP_HOST`
- `SMTP_PORT`
- `SMTP_SECURE`
- `SMTP_USERNAME`
- `SMTP_PASSWORD`
- `SMTP_FROM`

Optional key:

- `SMTP_FROM_NAME`

Set `SMTP_PASSWORD` to your SMTP app password in your local `.env`. For Gmail SMTP, use a generated app password. Do not use your normal account password.

## Load The Module

In your own PHP handler, require the module files:

```php
require_once __DIR__ . '/../mailer/src/EnvLoader.php';
require_once __DIR__ . '/../mailer/src/MailerConfig.php';
require_once __DIR__ . '/../mailer/src/Mailer.php';

$config = MailerConfig::fromEnv(__DIR__ . '/../mailer/.env');
$mailer = new Mailer($config);
```

If your PHPMailer folder is not beside `src/`, pass a custom PHPMailer source path:

```php
$mailer = new Mailer($config, __DIR__ . '/../vendor/phpmailer/src');
```

You can also build config directly from an array:

```php
$config = MailerConfig::fromArray([
    'SMTP_HOST' => 'smtp.gmail.com',
    'SMTP_PORT' => '465',
    'SMTP_SECURE' => 'ssl',
    'SMTP_USERNAME' => 'sender@example.com',
    'SMTP_PASSWORD' => '',
    'SMTP_FROM' => 'sender@example.com',
    'SMTP_FROM_NAME' => 'Your Website',
]);
```

## Public API

```php
$mailer->sendTo(
    string $email,
    string $subject,
    string $message,
    array $options = []
): array;
```

```php
$mailer->sendToMany(
    array $emails,
    string $subject,
    string $message,
    array $options = []
): array;
```

```php
$mailer->sendBulkFromDatabase(
    mysqli $conn,
    string $query,
    string $subject,
    string $message,
    array $options = []
): array;
```

Supported options:

```php
[
    'is_html' => true,
    'from_name' => 'Custom Sender Name',
]
```

- `is_html` must be a boolean. It defaults to `true`.
- `from_name` must be a string. It overrides `SMTP_FROM_NAME` for one send call.
- Sender display names containing line breaks are rejected.

## Result Shape

Every public send method returns:

```php
[
    'success' => true,
    'sent' => 1,
    'failed' => 0,
    'errors' => [],
]
```

`success` is `true` only when all intended recipients were sent successfully.

## Send To One Recipient

Put this in your own form handler, controller, or PHP action file:

```php
$result = $mailer->sendTo(
    'recipient@example.com',
    'Subject here',
    '<p>Message here</p>'
);

if (!$result['success']) {
    // Show a safe error message or log the result for your own system.
}
```

## Send To Multiple Recipients

```php
$result = $mailer->sendToMany(
    [
        'first-recipient@example.com',
        'second-recipient@example.com',
    ],
    'Subject here',
    '<p>Message here</p>',
    [
        'from_name' => 'Announcements',
    ]
);
```

The module sends one message per recipient so recipients are not exposed to each other.

## Send Plain Text

```php
$result = $mailer->sendTo(
    'recipient@example.com',
    'Plain text subject',
    "Plain text message body.",
    [
        'is_html' => false,
    ]
);
```

## Database-Backed Bulk Send

Use this when your existing system already has recipient emails in a database table.

```php
$conn = mysqli_connect('localhost', 'root', '', 'your_database');

$result = $mailer->sendBulkFromDatabase(
    $conn,
    'SELECT email FROM emails',
    'Bulk subject',
    '<p>Bulk message body.</p>'
);
```

The query must return a column named `email`.

Example valid queries:

```sql
SELECT email FROM emails
SELECT email FROM subscribers WHERE active = 1
```

Do not pass raw user input into the query. Build a trusted query or use your own prepared statements before calling the module.

## Examples

Optional example files are included:

```text
examples/target-send.php
examples/multi-target-send.php
examples/database-bulk-send.php
```

They are reference scripts. You can copy the pattern into your own handler instead of using the example files directly.

## PHPMailer Files

This branch vendors only the PHPMailer files required for SMTP sending:

```text
phpmailer/LICENSE
phpmailer/src/Exception.php
phpmailer/src/PHPMailer.php
phpmailer/src/SMTP.php
```

OAuth, POP-before-SMTP, language packs, Composer metadata, duplicate source folders, and zip artifacts are intentionally excluded.

## Not Included Yet

This module intentionally does not include attachments, templates, CC, BCC, reply-to handling, queues, retries, or built-in rate limiting.

Developers can add those features in their own systems or contribute them later. For production use, the host system should still handle authentication, authorization, CSRF protection, rate limiting, logging, and queueing when needed.

## Production Notes

- Never commit `.env`.
- Never commit real SMTP credentials.
- Protect the PHP handler that calls this module with your existing authentication, authorization, CSRF protection, and rate limiting.
- Validate and authorize users before allowing them to send email.
- For large recipient lists, consider queueing and provider rate limits in your host system.
- If a Gmail app password was ever committed or shared, revoke it and create a new one.
