# Scraping Engine Module

**ID:** REQ-MOD-001
**Status:** Completed
**Last Updated:** 2026-06-29

## Responsibility
Fetch raw HTML or JSON from target eCommerce sites and extract deal data.

## Implementation Details
- **Architecture:** Moved from server-side to a **Hybrid Local Worker** to ensure 100% free scraping without paying for proxies.
- **Worker Script:** A local script (Node.js/Python) runs on the admin's personal computer via Task Scheduler. Uses **Playwright or Puppeteer with Stealth plugins** to mimic real human browsing.
- **Ingestion Process (Real-Time WebSockets):** Instead of polling, the worker maintains a WebSocket connection to the Laravel server. When a URL is submitted via the Admin Dashboard, the server pushes an event, and the worker scrapes it instantly. The worker then uses Python Pillow to compose the final deal image and pushes the clean JSON and image back to the server.
- **Validation Loop (Continuous Expiry Checks):** The Local Worker continuously re-scrapes the URLs of deals currently marked `active`. If it detects an "Out of Stock" element or a price change back to M.R.P., it pings the server to auto-expire the deal.
- **Local State Management (Crash Resilience):** The worker uses a local `SQLite` database (`worker/state.db`) to track the queue of URLs (Pending, Processing, Completed, Failed). If the script crashes or loses internet, it resumes exactly where it left off.
- **Browser Session Caching:** Playwright saves and loads the browser context (`user_data_dir`) to reuse Amazon session cookies, acting as a trusted returning user to drastically reduce CAPTCHA triggers.
- **Home IP Protection (Backoff Limits):** To prevent the admin's personal residential IP from being banned, the script enforces strict concurrency limits, randomized delays (10-15 seconds between pages), and randomized human-like scrolling.
- **Manual CAPTCHA Intervention:** If the scraper hits a CAPTCHA that stealth plugins cannot bypass, it pauses the queue, plays an alert sound, and opens a visible browser window. The admin manually solves the CAPTCHA to resume the automated queue, keeping the solution 100% free.
- **Resilience:** If the local worker is offline, scraping is temporarily paused until it comes back online.
