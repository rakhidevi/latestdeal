# Environment Setup & CI/CD

**ID:** REQ-DEP-001
**Status:** Completed
**Last Updated:** 2026-06-29

## Environment Variables (`.env`)
Required critical variables:
- `DB_CONNECTION=mysql`
- `QUEUE_CONNECTION=database`
- `OPENAI_API_KEY=...`
- `TELEGRAM_BOT_TOKEN=...` (Master bot for platform alerts)

## CI/CD Pipeline
- **Local:** Developer runs `php artisan serve` and Vite for assets.
- **Production Push:** Code is pushed to `main` branch.
- **Deployment:** cPanel Git Version Control detects the push, pulls the code, and runs a `post-pull` hook to execute `composer install --no-dev` and `php artisan migrate --force`.
