# Deployment Strategy (MilesWeb cPanel)

**ID:** REQ-SRS-006
**Status:** Completed
**Last Updated:** 2026-06-29

## 1. Code Delivery
Since this will be hosted on MilesWeb standard PHP hosting (likely cPanel), we will utilize one of two methods:
- **Primary:** cPanel's built-in "Git Version Control" to pull directly from the repository.
- **Fallback:** Automated GitHub Action transferring files via SFTP on push to the `main` branch.

## 2. Environment Configuration
- The `.env` file will be securely stored above the public `public_html` directory.
- `APP_ENV` will be set to `production` and `APP_DEBUG` to `false` to ensure maximum performance and hide error traces.

## 3. Automation Setup (Crucial for Shared Hosting)
Because standard shared hosting does not typically allow running long-lived background daemons (like `supervisor`), we will configure a single Cron Job in cPanel:
```bash
* * * * * cd /path-to-your-project && /opt/cpanel/ea-php82/root/usr/bin/php artisan schedule:run >> /dev/null 2>&1
```
*Note: The exact path to the PHP 8.2 binary will be determined on the MilesWeb server.*
This cron will wake up Laravel every minute, which will then dispatch any pending ingestion or publishing jobs to the database queue.
