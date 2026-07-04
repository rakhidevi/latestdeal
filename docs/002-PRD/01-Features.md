# Feature Specifications

**ID:** REQ-PRD-001
**Status:** Completed
**Last Updated:** 2026-06-29

## 1. Data Ingestion & Processing
- **API Ingestion:** RESTful endpoints to receive deal payloads from external partners.
- **Web Scraping:** Automated scrapers for at least 3 major eCommerce platforms to extract price, original price, title, and image URLs.
- **Deduplication:** System must identify and merge duplicate deals from different sources.

## 2. Deal Management Engine (Core)
- **CRUD Operations:** Admins can create, read, update, and delete deals manually.
- **Tagging & Categorization:** Auto-tagging based on keywords (e.g., "Electronics", "Fashion").
- **Coupon Support:** Capability to ingest, store, and display promo codes. The frontend provides a 1-click "Copy Code & Go" UX.
- **Validation (Continuous):** The Local Worker continuously re-scrapes active deals. If the price returns to M.R.P. or goes out of stock, the system automatically sets `status = expired`.

## 3. AI Capabilities
- **Caption Generation:** LLM integration to read raw deal data and generate engaging, emoji-rich social media captions.
- **Image Composition:** Automated generation of composite images (product photo + price + discount badge). Handled entirely by the Local Worker (Python Pillow) to reduce server load.

## 4. Publisher Tools (Affiliate Dashboard)
- **Multi-Platform Integration:** Support for automated posting to Telegram (Channels & Groups), X.com, Facebook, and Instagram.
- **Click-to-Share:** Manual 1-click sharing buttons for WhatsApp and Snapchat with pre-formatted text.
- **Rule-Based Automation:** Publishers can create rules (e.g., "Post deals > 50% off in Electronics every 2 hours").
- **Affiliate Tag Replacement:** The system automatically identifies the merchant, looks up the publisher's `storeId`, and rebuilds the URL with the correct affiliate tag before posting.
- **Affiliate Link Generator:** A manual tool for publishers to instantly generate a tracked short-link for any random product URL they find.

## 5. Web Frontend (LatestDeal.in)
- **Curated Feed:** A clean, mobile-responsive feed of active deals.
- **Search & Filtering:** Users can search for specific products or filter by category and discount percentage.
- **Deal Expiry UI:** Clear visual indicators for deals that are expiring soon or have sold out.
- **Price History Graphs:** Visual line charts showing the deal's price over the last 30 days, building trust that the current price is a genuine drop.

## 6. Shopper Retention
- **Push Notifications:** Web push notifications (via OneSignal) to alert subscribers of flash deals.
- **Email Newsletter:** Capture forms to build an email list of direct subscribers for top weekly deals.
- **Price Alerts:** Users can set target prices for specific keywords. The system automatically notifies them when a matching deal is scraped.

## 7. Reporting & Analytics
- **Publisher Analytics:** Dashboard displaying total clicks, CTR, and estimated earnings per post/campaign.
- **System Metrics:** Admin view of system health, processing times, and API ingestion volumes.

## 8. SEO & Organic Growth
- **Structured Data:** Automated generation of JSON-LD `Product` and `Offer` schema for all deal pages to enhance Google Search visibility.
- **Dynamic Sitemaps:** Automatically updated XML sitemaps that ping search engines upon new deal ingestion.
