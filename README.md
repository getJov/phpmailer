# Mailer

Mailer is a lightweight PHP application for storing recipient emails in MySQL and sending one message to all saved recipients through PHPMailer SMTP.

## Requirements

- PHP with the `mysqli` extension enabled
- MySQL or MariaDB
- A local web server that can run PHP
- SMTP credentials, such as a Gmail account with a generated app password

## Setup

1. Import the database dump:

   ```bash
   mysql -u root -p mailer < db/mailer.sql
   ```

   If the `mailer` database does not exist yet, create it first:

   ```sql
   CREATE DATABASE mailer;
   ```

2. Copy the environment template:

   ```bash
   cp .env.example .env
   ```

3. Update `.env` for your system:

   ```env
   DB_HOST=localhost
   DB_USERNAME=root
   DB_PASSWORD=
   DB_NAME=mailer

   SMTP_HOST=smtp.gmail.com
   SMTP_PORT=465
   SMTP_SECURE=ssl
   SMTP_USERNAME=your-email@example.com
   SMTP_PASSWORD=your-new-app-password
   SMTP_FROM=your-email@example.com
   ```

   Required keys:

   - `DB_HOST`
   - `DB_USERNAME`
   - `DB_PASSWORD`
   - `DB_NAME`
   - `SMTP_HOST`
   - `SMTP_PORT`
   - `SMTP_SECURE`
   - `SMTP_USERNAME`
   - `SMTP_PASSWORD`
   - `SMTP_FROM`

4. For Gmail SMTP, create a new app password in your Google account and use that value for `SMTP_PASSWORD`. Do not use your normal account password.

5. Serve the project through your local PHP server or local web stack, then open:

   ```text
   home.html
   ```

## Usage

- Open `insert.php` to add recipient names and email addresses.
- Open `index.php` to view saved recipients and send an email to all valid recipients.
- The send flow requires complete SMTP values in `.env`.

## PHPMailer Files

This project vendors only the PHPMailer files required for the current SMTP flow:

```text
phpmailer/LICENSE
phpmailer/src/Exception.php
phpmailer/src/PHPMailer.php
phpmailer/src/SMTP.php
```

OAuth, POP-before-SMTP, DSN configuration, language packs, Composer metadata, duplicate source folders, and zip artifacts are intentionally excluded.

## Security Notes

- Never commit `.env`.
- Never commit real SMTP credentials.
- Never commit real recipient seed data in `db/mailer.sql`.
- If a Gmail app password was ever committed or shared, revoke it and create a new one.
- This app does not include authentication, CSRF protection, rate limiting, or deployment hardening.
