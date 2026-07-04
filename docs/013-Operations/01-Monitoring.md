# Monitoring & Logging

**ID:** REQ-OPS-001
**Status:** Completed
**Last Updated:** 2026-06-29

## 1. Application Logs
- Standard Laravel logs (`storage/logs/laravel.log`) rotated daily.
- All failed queue jobs are logged to the `failed_jobs` database table.

## 2. Uptime Monitoring
- Use a free external service (e.g., UptimeRobot) to ping the public homepage every 5 minutes.
- If the site is down, an alert is sent to the Admin email.

## 3. Queue Monitoring
- Since we don't have Laravel Horizon (Redis required), we will build a simple Admin dashboard view that queries `SELECT count(*) FROM jobs` to visualize queue backup.
