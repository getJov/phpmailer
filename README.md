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

## Add This To An Existing PHP Website

Use this project as a small PHP/MySQL mailer module. The easiest integration is to place it in a folder inside an existing PHP site, for example:

```text
your-site/
  mailer/
    connect.php
    send.php
    insert.php
    index.php
    db/mailer.sql
    phpmailer/
    .env.example
```

If the existing website will use this module's recipient management pages, copy:

```text
connect.php
send.php
insert.php
index.php
db/mailer.sql
phpmailer/
.env.example
```

If the existing website already manages recipients in its own database table, copy only the sending pieces:

```text
connect.php
send.php
phpmailer/
.env.example
```

Then either import `db/mailer.sql` into the existing database, or update `send.php` so the recipient query matches the existing site table.

Current recipient query:

```php
SELECT email FROM emails
```

Example for an existing subscribers table:

```php
SELECT email FROM subscribers
```

To link to the module pages from an existing site:

```html
<a href="/mailer/insert.php">Add Recipient</a>
<a href="/mailer/index.php">Send Email</a>
```

To use an existing page as the send form, post to `send.php` with `subject`, `message`, and a submit control named `send`:

```html
<form action="/mailer/send.php" method="post">
  <input type="text" name="subject" required>
  <textarea name="message" required></textarea>
  <button type="submit" name="send">Send</button>
</form>
```

Make sure the `.env` file is in the same folder as `connect.php`, or update `connect.php` to load the environment file from the correct path.

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
