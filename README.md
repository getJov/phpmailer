# Target Send Mailer

This branch is the `target-send` starter version of the PHP mailer. It sends one message to one or more email addresses entered directly in the form.

This branch does not use a database and does not read from the bulk `emails` table.

## Requirements

- PHP
- A local web server that can run PHP
- SMTP credentials, such as a Gmail account with a generated app password

## Setup

1. Copy the environment template:

   ```bash
   cp .env.example .env
   ```

2. Fill in SMTP settings in `.env`:

   ```env
   SMTP_HOST=smtp.gmail.com
   SMTP_PORT=465
   SMTP_SECURE=ssl
   SMTP_USERNAME=
   SMTP_PASSWORD=
   SMTP_FROM=
   ```

   Required keys:

   - `SMTP_HOST`
   - `SMTP_PORT`
   - `SMTP_SECURE`
   - `SMTP_USERNAME`
   - `SMTP_PASSWORD`
   - `SMTP_FROM`

3. For Gmail SMTP, create a new app password in your Google account and use that value for `SMTP_PASSWORD`. Do not use your normal account password.

4. Serve the project through your local PHP server or local web stack, then open:

   ```text
   index.php
   ```

## Usage

Enter one or more recipient emails in the `Recipients` field.

Accepted formats:

```text
first@example.com, second@example.com
```

```text
first@example.com
second@example.com
```

The app validates all recipients before sending. If any recipient is invalid, the send is stopped before PHPMailer sends anything.

## Files

Required runtime files:

```text
config.php
index.php
send.php
phpmailer/LICENSE
phpmailer/src/Exception.php
phpmailer/src/PHPMailer.php
phpmailer/src/SMTP.php
```

This branch intentionally excludes the bulk-send files:

```text
connect.php
insert.php
home.html
db/mailer.sql
```

## Security Notes

- Never commit `.env`.
- Never commit real SMTP credentials.
- This starter page has no authentication, CSRF protection, rate limiting, or production hardening.
- Protect this page inside your host system before using it in production.
