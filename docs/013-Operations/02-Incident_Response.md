# Incident Response

**ID:** REQ-OPS-002
**Status:** Completed
**Last Updated:** 2026-06-29

## Tier 1 (Critical) - Platform Down / Queue Halted
- **Trigger:** UptimeRobot reports LatestDeal.in is offline, OR queue size exceeds 1,000 pending jobs.
- **Action:** Admin logs into cPanel via SSH. Checks Nginx/Apache status and MySQL status. Restarts PHP-FPM if necessary. Checks `failed_jobs` table to see if an API integration is blocking the queue.

## Tier 2 (Warning) - Scrapers Failing
- **Trigger:** No new deals ingested in the last 4 hours.
- **Action:** Check if target eCommerce sites changed their HTML DOM layout. Update the CSS selectors in the `Scraper` classes and push a hotfix.
