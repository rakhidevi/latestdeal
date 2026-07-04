# Non-Functional Requirements (NFRs)

**ID:** REQ-PRD-003
**Status:** Completed
**Last Updated:** 2026-06-29

## 1. Performance
- **Processing Latency:** The time from a deal entering the ingestion queue to being fully processed (AI caption generated, image created) must be under 5 seconds.
- **Web Load Time:** The public frontend (LatestDeal.in) must achieve a First Contentful Paint (FCP) of < 1.5 seconds on mobile networks.

## 2. Scalability
- **Traffic Handling:** The web frontend must support up to 10,000 concurrent users during peak sale events (e.g., Prime Day).
- **Data Volume:** The database must efficiently handle the ingestion and archiving of up to 50,000 deals per day.

## 3. Reliability & Availability
- **Uptime:** Core APIs and the automated publishing engine must maintain 99.9% uptime.
- **Failover:** If an AI provider (e.g., OpenAI) fails, the system must fallback to a basic template-based caption generator to ensure publishing continues.

## 4. Security
- **Data Protection:** Publisher API keys and bot tokens must be encrypted at rest.
- **Rate Limiting:** Public APIs and scraping endpoints must implement strict rate limiting to prevent abuse and DDoS attacks.
- **Compliance:** The system must comply with the Terms of Service of all integrated affiliate networks (e.g., Amazon Associates policies on scraping and link cloaking).

## 5. Localization & Internationalization
- **Language:** English only for the MVP platform UI and AI-generated captions.
- **Currency:** Deals will display prices in the native currency of the scraped eCommerce platform (e.g., INR for Amazon India, USD for Amazon US). Multi-currency conversion is out of scope for MVP.
