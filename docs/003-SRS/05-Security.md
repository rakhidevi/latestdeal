# Security Architecture

**ID:** REQ-SRS-005
**Status:** Completed
**Last Updated:** 2026-06-29

## 1. Web Security (Native Laravel Protections)
By leveraging Laravel 11.x, the platform natively mitigates standard OWASP top 10 threats:
- **SQL Injection:** All database queries will use Laravel Eloquent ORM or the Query Builder, which utilize PDO parameter binding.
- **XSS (Cross-Site Scripting):** Blade templates automatically escape all output using `{{ }}` tags.
- **CSRF:** All POST/PUT/DELETE requests in the web dashboard require a valid CSRF token.

## 2. Sensitive Data Storage
- **Passwords:** Hashed using Bcrypt (Laravel default).
- **Publisher API Keys (Telegram/Twitter):** Encrypted at rest in the database using Laravel's `Crypt` facade. They are decrypted dynamically only when the background queue is ready to post a message.

## 3. Authorization & Roles
- **Middleware:** A strict `is_admin` middleware will protect all back-office routes. Publishers will only have access to their own data via scope checking.
