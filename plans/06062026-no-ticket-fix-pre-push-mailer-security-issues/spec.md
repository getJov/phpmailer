# Fix Pre-Push Mailer Security Issues

## Why
The current mailer app contains hardcoded SMTP credentials, real email seed data, unsafe HTML output, weak input validation, and duplicate vendored PHPMailer artifacts. These must be fixed before pushing to GitHub so the repository is safer, cleaner, and less likely to break on deployment.

## What
Prepare the app for a GitHub push by moving sensitive configuration to `.env`, adding `.gitignore`, removing sensitive seed data, normalizing table casing, escaping rendered database values, validating inserted emails, handling DB/SMTP failures, trimming PHPMailer to required runtime files, and adding future-use documentation.

## Context

**Relevant files:**
- `connect.php` - creates the MySQL connection with hardcoded local credentials.
- `send.php` - loads PHPMailer, reads recipients from the database, and sends email through Gmail SMTP.
- `insert.php` - inserts recipient names and emails into the database.
- `index.php` - displays saved recipients and renders the send-email form.
- `db/mailer.sql` - database dump containing the `emails` table and real seed email addresses.
- `phpmailer/` - active PHPMailer source required by `send.php`; only `LICENSE`, `src/Exception.php`, `src/PHPMailer.php`, and `src/SMTP.php` are required for the current SMTP app-password flow.
- `PHPMailer-master/`, `PHPMailer-master.zip`, `PHPMailer-master.zip:Zone.Identifier` - duplicate PHPMailer artifacts that should not be pushed.
- `README.md` - new project usage guide for future systems.

**Patterns to follow:**
- Keep this as a simple procedural PHP app; do not introduce a framework.
- Continue using `mysqli` unless a later plan intentionally modernizes database access.
- Keep PHPMailer loaded from direct `phpmailer/src/*.php` requires; Composer adoption is out of scope.

**Key decisions already made:**
- Create a local `.env` for secrets and add it to `.gitignore`.
- Add a safe `.env.example` so GitHub has the required config keys without real values.
- Standardize SQL table references to lowercase `emails`.
- Do not commit real SMTP passwords or real recipient seed data.
- Trim vendored PHPMailer to the files required by `send.php`: `phpmailer/LICENSE`, `phpmailer/src/Exception.php`, `phpmailer/src/PHPMailer.php`, and `phpmailer/src/SMTP.php`.
- Add a root `README.md` with setup, configuration, database import, SMTP, local usage, and excluded-file guidance.

## Constraints

**Must:**
- Keep `.env` local-only and ignored by Git.
- Move SMTP username/password and database connection values out of PHP source.
- Use these config keys: `DB_HOST`, `DB_USERNAME`, `DB_PASSWORD`, `DB_NAME`, `SMTP_HOST`, `SMTP_PORT`, `SMTP_SECURE`, `SMTP_USERNAME`, `SMTP_PASSWORD`, and `SMTP_FROM`.
- Do not copy the exposed Gmail app password into `.env`; `.env` may contain local DB defaults and blank/new SMTP placeholders until a regenerated app password is supplied.
- Revoke or regenerate the existing Gmail app password outside the codebase before trusting the repo.
- Escape database values before rendering them in HTML.
- Validate email addresses before inserting them.
- Handle failed database connections, failed database queries, and PHPMailer exceptions without dumping sensitive internals to users.
- Keep the current app behavior: add recipients, list recipients, send one message to all saved recipients.

**Must not:**
- Do not introduce Laravel, Symfony, Composer restructuring, or new dependencies.
- Do not refactor unrelated UI or rewrite the app architecture.
- Do not commit `.env`.
- Do not keep duplicate PHPMailer source folders or zip artifacts in the repo.
- Do not keep unnecessary PHPMailer runtime extras for OAuth, POP-before-SMTP, DSN configuration, language packs, or Composer metadata.

**Out of scope:**
- User authentication, authorization, CSRF protection, rate limiting, audit logging, and deployment automation.
- Full dependency/CVE audit of PHPMailer.
- Replacing `mysqli` with PDO.
- Creating a polished UI.
- Pushing to GitHub; this plan prepares the files, but remote creation and push are separate.

## Risk

**Level:** 3

**Risks identified:**
- Existing Gmail app password is already exposed in source → **Mitigation:** remove it from code and require credential revocation/regeneration outside the repo.
- `.env` parsing can become a fragile homemade config layer → **Mitigation:** implement only a minimal line-based loader for simple `KEY=value` pairs, document supported format in `.env.example`, and avoid clever parsing.
- Table casing mismatch can break across environments → **Mitigation:** standardize code and SQL dump to lowercase `emails`.
- Removing PHPMailer files could break runtime mail sending → **Mitigation:** keep `phpmailer/LICENSE`, `phpmailer/src/Exception.php`, `phpmailer/src/PHPMailer.php`, and `phpmailer/src/SMTP.php`; verify those classes load before declaring completion.
- The workspace currently has invalid Git metadata, so `.env` ignore behavior and task-level commits may not work until Git is initialized or repaired → **Mitigation:** include a Git repository bootstrap task before app changes and keep GitHub push out of scope.
- This is large by file count because vendor artifacts are being deleted → **Mitigation:** keep the behavioral code changes small and treat the vendor trim as a mechanical cleanup.

**Pushback:**
- Moving the password to `.env` is necessary but not sufficient. That Gmail app password has already lived in source, so it should be treated as compromised. Future-us will hate pretending this is fixed without rotating it.
- The app still has broader web-security gaps like CSRF protection and authentication. Those are out of scope here, but this should not be deployed publicly as-is.
- Keeping a manually vendored PHPMailer copy is not ideal. Composer would be cleaner, but switching dependency management is a separate decision and would expand this fix.

## Tasks

### T0: Establish Local Git Safety
**Do:** If `git rev-parse --show-toplevel` fails, run `git init` before app changes so `.gitignore` behavior and task-level commits can be verified. If existing `.git/` metadata blocks initialization, halt and ask before deleting files or changing permissions. Do not create a remote or push.
**Files:** `.git/`
**Verify:** `git rev-parse --show-toplevel`

### T1: Add Local Configuration Boundary
**Do:** Add `.gitignore`, add `.env.example`, create local `.env` with local DB defaults and blank/new SMTP placeholders, and update `connect.php` / `send.php` to read database and SMTP settings from environment config instead of hardcoded values.
**Files:** `.gitignore`, `.env.example`, `.env`, `connect.php`, `send.php`
**Verify:** `php -l connect.php` and `php -l send.php`; `git check-ignore .env`; `rg -n "@[A-Za-z0-9._%+-]+\\.[A-Za-z]{2,}|Password\\s*=\\s*['\\\"][^'\\\"]+['\\\"]|Username\\s*=\\s*['\\\"][^'\\\"]+['\\\"]|setFrom\\(\\s*['\\\"][^'\\\"]+['\\\"]" --glob '!plans/**' --glob '!phpmailer/**' --glob '!README.md' --glob '!.env' .` returns no hardcoded SMTP account, SMTP password, or real recipient seed data.

### T2: Harden Recipient Input And Output
**Do:** Validate inserted emails with server-side email validation, use lowercase `emails` table references, escape displayed recipient data, remove real recipient seed rows from the SQL dump, reset dump seed state to an empty `emails` table, and avoid printing raw SQL/database errors to users.
**Files:** `insert.php`, `index.php`, `send.php`, `db/mailer.sql`
**Verify:** `php -l insert.php`, `php -l index.php`, `php -l send.php`

### T3: Handle Mail And Database Failures
**Do:** Add controlled handling for failed database connections, failed recipient queries, empty recipient lists, invalid recipient rows, missing SMTP config, and PHPMailer send exceptions.
**Files:** `connect.php`, `send.php`
**Verify:** `php -l connect.php`, `php -l send.php`; Manual: with missing SMTP config, submit the send form and confirm safe failure messaging.

### T4: Clean Pre-Push Repository Artifacts
**Do:** Remove duplicate PHPMailer artifacts and trim the active `phpmailer/` folder to only `LICENSE`, `src/Exception.php`, `src/PHPMailer.php`, and `src/SMTP.php`.
**Files:** `PHPMailer-master/`, `PHPMailer-master.zip`, `PHPMailer-master.zip:Zone.Identifier`, `phpmailer/`
**Verify:** `test -f phpmailer/LICENSE && test -f phpmailer/src/Exception.php && test -f phpmailer/src/PHPMailer.php && test -f phpmailer/src/SMTP.php`; `test ! -e PHPMailer-master && test ! -e PHPMailer-master.zip && test ! -e 'PHPMailer-master.zip:Zone.Identifier'`; `php -r "require 'phpmailer/src/Exception.php'; require 'phpmailer/src/PHPMailer.php'; require 'phpmailer/src/SMTP.php'; var_dump(class_exists('PHPMailer\\\\PHPMailer\\\\PHPMailer'), class_exists('PHPMailer\\\\PHPMailer\\\\SMTP'), class_exists('PHPMailer\\\\PHPMailer\\\\Exception'));"`

### T5: Add Project Usage README
**Do:** Create `README.md` with setup instructions for future systems: requirements, database import, `.env` setup, Gmail app-password setup, local run steps, recipient insert flow, send flow, kept/removed PHPMailer files, and security notes about not committing secrets or real recipient data.
**Files:** `README.md`
**Verify:** `README.md` documents every key from `.env.example` and contains no real credentials or real recipient emails.

## Done
- [ ] `.env` exists locally and is ignored by Git.
- [ ] `.env.example` contains only placeholder values.
- [ ] No SMTP password, Gmail account, or real seed recipient email remains in source files intended for GitHub.
- [ ] Code and SQL dump consistently reference the lowercase `emails` table.
- [ ] SQL dump contains an empty `emails` table with no real seed recipients.
- [ ] Recipient list output escapes database values before rendering.
- [ ] Insert flow rejects invalid emails server-side.
- [ ] Mail send flow handles DB/SMTP failures without raw error dumps.
- [ ] Duplicate PHPMailer artifacts are removed; active `phpmailer/` contains only required runtime files plus `LICENSE`.
- [ ] PHPMailer classes required by `send.php` still load after trimming.
- [ ] `php -l connect.php`, `php -l send.php`, `php -l insert.php`, and `php -l index.php` pass.
- [ ] `README.md` documents setup, `.env`, database import, SMTP setup, local usage, and excluded files.
- [ ] Manual: with missing SMTP config, the send flow shows safe failure messaging.
- [ ] Manual: after the owner supplies a regenerated Gmail app password in `.env`, adding a recipient and sending an email works.
- [ ] Owner confirms the old exposed Gmail app password has been revoked or replaced outside the codebase.

## Revision Log

### Rev 1 - June 6, 2026
**Change:** Tightened the spec to remove exact leaked values from verification, define required `.env` keys, handle invalid local Git metadata, trim PHPMailer to the minimal required runtime files, add README documentation, and clarify SMTP credential rotation.
**Reason:** Spec-governance review found execution blockers and the user added requirements for PHPMailer trimming safety and future-system README instructions.
**Updated Done criteria:** Added PHPMailer class-load verification, README coverage, safe missing-SMTP behavior, empty SQL seed data, and owner confirmation that the old Gmail app password has been revoked or replaced.

### Rev 2 - June 6, 2026
**Change:** Tightened the hardcoded-secret verification regex so env-backed PHPMailer assignments are allowed while literal SMTP accounts, literal SMTP passwords, and literal recipient emails still fail the scan.
**Reason:** Execution showed the previous regex matched safe variable assignments and would block correct implementation.
**Updated Done criteria:** No Done criteria changed.
