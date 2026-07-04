# Risks & Mitigations

**ID:** REQ-BIZ-008
**Status:** Completed
**Last Updated:** 2026-06-29

## Business Risks

### 1. Affiliate Network Bans
- **Risk:** Major networks (e.g., Amazon) can terminate affiliate accounts for compliance violations.
- **Mitigation:** Strict enforcement of API usage guidelines, clear disclaimers on all pages, and avoidance of prohibited scraping methods. Diversify affiliate networks to avoid single points of failure.

### 2. API Rate Limiting & Blocking (Home IP Risk)
- **Risk:** eCommerce platforms may block LatestDeal's IP addresses during data ingestion. Since we use a residential Hybrid Worker, Amazon could IP-ban the Admin's personal home network.
- **Mitigation:** Relying on a **Hybrid Local Worker** architecture equipped with **Playwright Stealth**, strict concurrency limits, and randomized **Backoff Delays** (10-15 seconds between pages) to act exactly like a human shopper, bypassing enterprise bot-protections 100% for free while keeping the home IP safe.

## Technical Risks

### 1. AI Output Quality
- **Risk:** AI-generated captions or images may hallucinate or produce inappropriate content for brands.
- **Mitigation:** Implement strict prompt engineering protocols, mandatory moderation queues for high-risk categories, and automated keyword blacklists.

### 2. Traffic Spikes (Flash Sales)
- **Risk:** Massive traffic surges during events like "Black Friday" leading to downtime.
- **Mitigation:** Cloud-native architecture utilizing aggressive caching (Redis/CDN), decoupled background job queues (RabbitMQ/SQS), and auto-scaling database instances.
