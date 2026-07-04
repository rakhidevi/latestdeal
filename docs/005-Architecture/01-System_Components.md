# System Components

**ID:** REQ-ARC-001
**Status:** Completed
**Last Updated:** 2026-06-29

## 1. Web Layer (Nginx/Apache)
- Receives incoming HTTP requests.
- Serves static assets directly.
- Proxies PHP requests to PHP-FPM.

## 2. Application Layer (Laravel)
- **Controllers:** Handle routing and business logic.
- **Console Kernel:** Manages scheduled scraping and queue processing.
- **Blade Views:** Renders the frontend UI.

## 3. Data Layer (MySQL)
- Stores all persistence data (Users, Deals, Rules, Queued Jobs).
- Used as the cache layer for high-speed page loads.

## 4. Integration Layer (APIs)
- Outbound requests to Telegram, X.com, Meta (Graph API).
- API endpoints to sync with the Local Worker (Playwright/Puppeteer Headless Scraping & Local Ollama AI).
- **WebSocket Server (Reverb/Pusher):** Broadcasts real-time events to the Local Worker for instant on-demand scraping.

## 5. Image Management Layer
- **Worker-Side Generation:** To save server CPU, composite images are generated on the Local Worker using Python `Pillow` and uploaded to the server via API.
- **Ephemeral Local Storage:** Images are stored locally on the server.
- **Aggressive Pruning:** A strict CRON job enforces a 72-hour maximum lifespan for all images, auto-deleting them to maintain the 100% free/shared hosting constraint.
