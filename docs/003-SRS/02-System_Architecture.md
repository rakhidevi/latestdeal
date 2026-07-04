# System Architecture

**ID:** REQ-SRS-002
**Status:** Completed
**Last Updated:** 2026-06-29

## 1. High-Level Architecture
Given the constraints of PHP hosting, the system follows a Monolithic MVC (Model-View-Controller) architecture using Laravel, with strong decoupling of background tasks to ensure the public frontend remains incredibly fast.

### Component Breakdown:
1. **Public Frontend (Blade/HTML):** Serves the main deal feed to shoppers. Highly cached.
2. **Admin/Publisher Dashboard:** Protected routes for managing deals and API integrations.
3. **Ingestion & AI Engine (Hybrid Local Worker):** A lightweight script running on the admin's local machine that handles scraping (residential IP) and caption generation (local Ollama), pushing results back to the API.
4. **Publishing Engine (Queue Workers):** Jobs pushed to the database queue. A cron job processes these queues to send messages/images to Telegram/Twitter without blocking the main web server.

## 2. The Data Flow
1. **Fetch:** Local Worker fetches URLs to scrape from the server API.
2. **Process:** Local Worker scrapes the deals using residential IP and generates an AI caption using local Ollama. It posts the finalized deal back to the server. Image is composited on the server.
3. **Store:** Deal is saved to MySQL.
4. **Distribute:** A `PublishDealToSocial` job is dispatched to the DB Queue.
5. **Serve:** A shopper visits LatestDeal.in. The Laravel Controller pulls the active deals from the Cache (avoiding DB queries for every user) and renders the Blade view in milliseconds.
