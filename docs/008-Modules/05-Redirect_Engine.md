# Redirect & Cloaking Engine

**ID:** REQ-MOD-005
**Status:** Completed
**Last Updated:** 2026-07-03

## Responsibility
Provide cloaked short-links for all deals to protect against social media spam filters, bypass ad-blockers, and enable 100% accurate end-to-end click tracking.

## Implementation Details
- **Routing:** A public route `GET /go/{deal_id}` (or a hashed slug) acts as the entry point for all outbound traffic.
- **Click Logging:** Before redirecting, the engine captures the user's `ip_address`, `deal_id`, and `publisher_integration_id` (passed via query parameter, e.g., `?pub=123`) and inserts a record into the `clicks` table.
- **URL Rebuilding:** The engine fetches the merchant's base URL and dynamically rebuilds the query string to append the correct `storeId` or affiliate parameter based on the publisher.
- **HTTP 302 Redirect:** The engine responds with an `HTTP 302 Found` status code, instantly forwarding the user to the final merchant destination. We use 302 instead of 301 to ensure browsers don't cache the redirect, forcing every click to be tracked by our server.
- **Bot Filtering:** Implement basic user-agent sniffing to ignore clicks from known bots (e.g., Googlebot, Facebook Crawler) to ensure publisher analytics remain clean and accurate.
