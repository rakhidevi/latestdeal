# API Endpoints

**ID:** REQ-API-001
**Status:** Completed
**Last Updated:** 2026-06-29

## Internal APIs (Authenticated via Long-Lived Bearer Token)

### 1. `POST /api/v1/deals/ingest`
- **Purpose:** Allow the trusted Local Worker to push new deals and composite images securely to production without FTP.
- **Authentication:** Requires `Authorization: Bearer <API_KEY>` header. The API key is generated via Laravel Sanctum for the Local Worker.
- **Payload Structure (JSON):**
  ```json
  {
      "title": "Apple iPhone 15",
      "original_price": 79900.00,
      "discounted_price": 65900.00,
      "coupon_code": null,
      "url": "https://amazon.in/...",
      "category_id": 2,
      "merchant_id": 1,
      "ai_caption": "🚨 Huge Drop on iPhone!...",
      "image_base64": "data:image/jpeg;base64,/9j/4AAQSkZJ..."
  }
  ```
- **Response:** `201 Created` with the generated Deal ID.

### 2. `GET /api/v1/publisher/metrics`
- **Purpose:** Fetch click data for publisher dashboards.
- **Response:** `{"total_clicks": 150, "ctr": "4.5%"}`

## Real-Time WebSockets (Laravel Reverb / Pusher)

### 1. Channel: `private-scraper-worker`
- **Event:** `DealScrapeRequested`
- **Purpose:** Allows the Admin Dashboard to instantly ping the Local Worker to scrape a specific URL without waiting for cron polling.
- **Payload:** `{"url": "https://amazon.in/...", "force_scrape": true}`

## Public APIs (No Authentication)

### 1. `GET /go/{deal_id}`
- **Purpose:** Public tracking endpoint that logs a click and 302 redirects the user to the merchant affiliate link.
- **Query Params:** `?pub={publisher_integration_id}` (optional)
- **Response:** `HTTP 302 Found` (Redirect)

## External APIs (Integrations)
- **Telegram SendMessage:** `POST https://api.telegram.org/bot<token>/sendMessage`
- **Telegram SendPhoto:** `POST https://api.telegram.org/bot<token>/sendPhoto`
