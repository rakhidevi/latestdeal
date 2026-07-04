# Product Vision

**ID:** REQ-FND-001
**Status:** Completed
**Last Updated:** 2026-06-29

## Vision Statement
LatestDeal aims to become the premier, highly scalable deals and affiliate aggregation platform. By combining AI-driven curation, robust automation, and a deeply personalized user experience, LatestDeal connects shoppers with the highest-value offers while providing publishers and affiliates with powerful monetization engines.

## Elevator Pitch
LatestDeal is an AI-powered deal aggregation platform that curates the best discounts for shoppers while providing affiliates with fully automated tools to monetize their audiences.

## Target Audience
- **Shoppers:** Individuals looking for curated, high-quality discounts and deals across various product categories.
- **Affiliates/Publishers:** Content creators and marketers looking for a reliable, automated platform to distribute deals and track conversions.
- **Merchants:** Brands seeking targeted visibility for their promotions.

## Core Value Proposition
- **For Shoppers:** A noise-free, highly relevant feed of deals tailored to their interests, eliminating the friction of manual searching.
- **For Affiliates:** A fully automated suite of tools (social publishing, AI captions, image generation) to scale their promotional efforts without manual overhead.

## Key Differentiators
1. **AI-First Approach:** Utilizing AI for deal categorization, caption generation, and image optimization.
2. **Commercial-Grade Architecture:** Built for high concurrency, multi-tenant scaling, and robust API integrations from day one.
3. **Automated Publishing:** Seamless integration with social engines to push deals across networks autonomously.
# Mission

**ID:** REQ-FND-002
**Status:** Completed
**Last Updated:** 2026-06-29

## Mission Statement
To democratize access to the best deals and savings online, while empowering publishers to monetize their audiences effortlessly through intelligent automation and robust APIs.

## Strategic Objectives
1. **Automate Deal Discovery & Delivery:** Reduce the manual effort required to find, format, and share deals to near-zero.
2. **Build a Scalable Foundation:** Architect a commercial-grade platform that can serve millions of requests and handle massive data synchronization seamlessly.
3. **Foster an Ecosystem:** Create extensible APIs that allow external developers and affiliates to plug into the LatestDeal engine.
# Problem Statement

**ID:** REQ-FND-003
**Status:** Completed
**Last Updated:** 2026-06-29

## The Problem
The current landscape of deal discovery and affiliate marketing is highly fragmented and labor-intensive.

### For Shoppers
- **Information Overload:** Shoppers are bombarded with notifications from noisy Telegram and WhatsApp groups.
- **Irrelevance & Expiry:** Deals are often irrelevant to the shopper's interests or expired by the time they are discovered.

### For Affiliates/Publishers
- **Manual Labor:** Finding high-quality deals, generating affiliate links, downloading images, and writing captions is a tedious, manual process.
- **Lack of Scale:** It is difficult to scale deal distribution across multiple platforms without spending hours formatting and posting.

LatestDeal solves this by automating the entire lifecycle of a deal, from discovery to distribution.
# Core Principles

**ID:** REQ-FND-004
**Status:** Completed
**Last Updated:** 2026-06-29

## Guiding Values

1. **Automation First:** If a task can be automated (e.g., categorizing a deal, writing a caption, formatting an image), it must be. We aim for zero manual overhead for our affiliates.
2. **API-Driven Architecture:** The platform must be built with a decoupled API-first approach, ensuring seamless integration for 3rd-party developers and external platforms.
3. **Noise-Free Curation:** The value of our platform is in its curation. We prioritize high-quality, relevant deals over raw volume, utilizing AI to filter out low-value noise.
4. **Performance & Scalability:** Designed for high concurrency from day one to support traffic spikes inherent to deal platforms (e.g., Black Friday sales).
# Business Goals

**ID:** REQ-BIZ-001
**Status:** Completed
**Last Updated:** 2026-06-29

## Primary Goals (Year 1)
1. **MVP Launch:** Launch the core deal aggregation and publishing platform (LatestDeal.in) with fully automated social integrations.
2. **Affiliate Onboarding:** Onboard the first 50 active publishers/affiliates to use the automated publishing tools.
3. **Revenue Generation:** Achieve self-sustainability through affiliate commissions and premium publisher subscriptions.
4. **Traffic Benchmarks:** Reach 100,000 monthly active shoppers across the platform and its network of published deals.

## Secondary Goals
1. **AI Refinement:** Achieve a 95% accuracy rate in AI-generated deal categorizations and social media captions.
2. **API Adoption:** Launch a public API for 3rd-party integrations and see active adoption by at least 10 external services.
# Success Metrics

**ID:** REQ-BIZ-002
**Status:** Completed
**Last Updated:** 2026-06-29

## Key Performance Indicators (KPIs)

### 1. Platform Engagement
- **Daily Active Users (DAU):** Target > 10,000 DAU.
- **Click-Through Rate (CTR):** Target > 12% CTR on deals published to social channels.
- **Time on Site:** Average > 3 minutes per session on LatestDeal.in.

### 2. Automation & AI Performance
- **Publishing Reliability:** > 99.9% uptime and success rate for automated social media posts.
- **Processing Time:** < 5 seconds from deal ingestion to fully formatted social ready post (caption + image).

### 3. Monetization & Growth
- **Affiliate Conversion Rate:** Target > 3% conversion rate on clicked outbound deal links.
- **Publisher Retention:** > 85% MoM retention rate for active affiliates utilizing the publishing engine.
- **Gross Merchandise Value (GMV):** Driven through affiliate links, targeting $1M+ in first year.
# Personas

**ID:** REQ-BIZ-003
**Status:** Completed
**Last Updated:** 2026-06-29

## 1. The Deal Hunter (Shopper)
- **Profile:** Highly motivated to save money. Checks multiple platforms daily for discounts on electronics, fashion, and everyday items.
- **Pain Points:** Overwhelmed by spam, expired coupons, and irrelevant offers.
- **Goal:** Find the lowest absolute price for desired products quickly and reliably.

## 2. The Power Affiliate (Publisher)
- **Profile:** Manages Telegram channels, WhatsApp groups, and Twitter accounts dedicated to deals.
- **Pain Points:** Spends hours manually formatting affiliate links, writing captions, and downloading/uploading images.
- **Goal:** Completely automate their workflow so they can focus on audience growth rather than manual posting.

## 3. The Platform Admin
- **Profile:** System operator ensuring LatestDeal runs smoothly.
- **Pain Points:** Managing server load, fixing broken API integrations, identifying spam or fraudulent deals.
- **Goal:** Have clear, actionable dashboards to monitor system health and resolve issues quickly.
# Competitive Analysis

**ID:** REQ-BIZ-004
**Status:** Completed
**Last Updated:** 2026-06-29

## Major Competitors

### 1. Traditional Deal Forums (e.g., Slickdeals, DesiDime)
- **Strengths:** Massive community, high SEO ranking, robust user-generated content.
- **Weaknesses:** Cluttered UI, manual deal submission process, difficult for new affiliates to monetize, mobile experience can be sub-par.
- **LatestDeal Advantage:** AI-driven curation (less clutter), automated publishing tools for affiliates, highly polished modern UI.

### 2. Coupon Aggregators (e.g., Honey, RetailMeNot)
- **Strengths:** Browser extensions, passive savings for users.
- **Weaknesses:** Focused on coupons at checkout, less focus on discovering raw product price drops.
- **LatestDeal Advantage:** Proactive deal discovery via social and direct feeds, targeting users *before* they are at the checkout page.

### 3. Niche Telegram/WhatsApp Groups
- **Strengths:** Immediate, push-notification delivery, highly engaged niche audiences.
- **Weaknesses:** Ephemeral (deals get lost in chat), hard to search history, manual work for admins.
- **LatestDeal Advantage:** We *empower* these groups rather than compete. LatestDeal acts as the backend engine for these admins to automate their groups.
# Scope (v1.0)

**ID:** REQ-BIZ-005
**Status:** Completed
**Last Updated:** 2026-06-29

## In-Scope Features for MVP

### 1. Data Ingestion
- REST APIs to ingest deals from external systems.
- Basic scraping capabilities for 3 major eCommerce platforms.

### 2. Deal Management Engine
- CRUD operations for deals.
- Tagging, categorization, and validation.

### 3. AI Capabilities
- AI-generated captions for social media.
- AI-based image cropping and composite generation (via Sharp).

### 4. Publisher Tools
- Dashboard to view available deals.
- One-click copy/paste of formatted affiliate content.
- Basic automated posting to Telegram via bot token.

### 5. Web Frontend (LatestDeal.in)
- Responsive web app displaying a curated feed of active deals.
- Search and category filters.
# Out-of-Scope (v1.0)

**ID:** REQ-BIZ-006
**Status:** Completed
**Last Updated:** 2026-06-29

## Explicitly Excluded Features

To ensure a timely and stable release of v1.0, the following features will NOT be included in the initial launch. They are deferred to the Future Roadmap (Phase 2+).

1. **Native Mobile Apps:** iOS and Android applications will not be developed. The web platform must be highly responsive and mobile-optimized as the primary interface.
2. **User Accounts for Shoppers:** v1.0 will not support login, profiles, or saved deals for standard shoppers. The platform will operate as an open, public aggregator.
3. **Complex Affiliate Multi-Tier Networks:** Advanced sub-affiliate tracking, MLM-style commission structures, and internal wallet systems are deferred.
4. **Browser Extensions:** Development of Chrome/Firefox extensions for auto-applying coupons is excluded.
5. **Real-time Bidding for Placements:** Sponsored deal placements will be managed manually by admins, not through an automated self-serve bidding portal.
# Monetization

**ID:** REQ-BIZ-007
**Status:** Completed
**Last Updated:** 2026-06-29

## Primary Revenue Streams

### 1. Direct Affiliate Commissions
- LatestDeal will append its own affiliate tags to organic deals posted on the main web platform.
- Revenue is generated directly from clicks leading to purchases on partner eCommerce sites (Amazon Associates, Flipkart Affiliate, etc.).

### 2. SaaS Subscriptions for Publishers (Pro Tier)
- Basic publishers can use the platform for free, with LatestDeal appending a secondary affiliate parameter or skimming a percentage of clicks (if legally/technically compliant).
- "Pro" publishers will pay a fixed monthly SaaS fee (e.g., $29/mo) to unlock API access, remove platform branding, and utilize unrestricted AI automation limits with their *own* raw affiliate tags.

### 3. Sponsored Placements
- Brands and PR agencies can pay for "Pinned Deals" or "Featured Placements" on the LatestDeal.in homepage and across our internal automated social channels.
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
# Go-To-Market (GTM) Strategy

**ID:** REQ-BIZ-009
**Status:** Completed
**Last Updated:** 2026-06-29

## The Cold Start Strategy

### 1. Affiliate Acquisition (B2B)
- **Target:** Existing Telegram/WhatsApp deal group admins with 5k-50k subscribers.
- **Approach:** Direct outreach offering a "1-month free Pro tier" to automate their posting. The pitch is time-saving: "Stop manually finding and posting deals. Let our bot do it for you, using your affiliate tags."
- **Goal:** Onboard 50 active publishers in the first 30 days.

### 2. Shopper Acquisition (B2C)
- **Target:** Deal hunters and general consumers looking for discounts.
- **Approach:** 
  - **SEO (Long-term):** Programmatically generate SEO-optimized pages for every deal, brand, and category.
  - **Social Referrals (Short-term):** As publishers share our auto-generated composite images, our subtle watermark ("Found via LatestDeal.in") drives organic traffic back to the platform.
- **Goal:** Reach 10,000 DAU within 3 months.
# User Journeys

**ID:** REQ-BIZ-010
**Status:** Completed
**Last Updated:** 2026-06-29

## 1. The Shopper Journey
1. **Discovery:** User searches for "iPhone 15 deals" on Google and lands on a LatestDeal product page, OR clicks a link shared by a publisher in a Telegram group.
2. **Evaluation:** User views the deal details, original price, discounted price, and AI-generated summary of why it's a good deal.
3. **Action:** User clicks "Get Deal", which redirects them to the merchant (e.g., Amazon) via an affiliate link.
4. **Retention:** User bookmarks the site or joins the official LatestDeal Telegram channel for future alerts.

## 2. The Publisher Journey
1. **Onboarding:** Publisher signs up and connects their Telegram channel / Twitter account via API keys.
2. **Configuration:** Publisher sets filters (e.g., "Only post Electronics", "Only deals > 50% off").
3. **Automation:** The LatestDeal engine automatically fetches deals, generates images, applies the publisher's affiliate tags, and posts to their channel.
4. **Monitoring:** Publisher logs into their dashboard to view click-through rates, earnings estimates, and adjust filtering rules.
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
# User Stories & Acceptance Criteria

**ID:** REQ-PRD-002
**Status:** Completed
**Last Updated:** 2026-06-29

## 1. Shopper (The Deal Hunter)

**US-01:** As a Shopper, I want to filter the deal feed by category so that I only see products I'm interested in.
- **AC:** Given I am on the feed, When I select 'Electronics', Then the feed reloads instantly to show only deals tagged as Electronics.

**US-02:** As a Shopper, I want to clearly see the original price and the discounted price so I can evaluate the value of the deal.
- **AC:** Given a deal card, Then I must see the original price with a strikethrough, the current price in bold, and a calculated percentage off badge.

**US-03:** As a Shopper, I want to click a deal and be taken directly to the merchant's site so I can make a purchase quickly.
- **AC:** Given a deal card, When I click 'Get Deal', Then a new tab opens to the merchant site with the correct affiliate tag appended.

## 2. Publisher (The Power Affiliate)

**US-04:** As a Publisher, I want to securely connect my Telegram channel via a Bot Token so the platform can post on my behalf.
- **AC:** Given the integration page, When I input a valid Telegram Bot Token and Chat ID, Then the system sends a test message and marks the channel as 'Active'.

**US-05:** As a Publisher, I want to set a filter to only post deals with a discount greater than 40%.
- **AC:** Given my automation settings, When I set minimum discount to 40%, Then the system will skip any deal under that threshold for my channel.

**US-06:** As a Publisher, I want my unique Amazon Associate ID to be automatically appended to the links.
- **AC:** Given a raw Amazon deal URL, When the system posts to my channel, Then the URL parameters are stripped and replaced exclusively with my Amazon Tag.

**US-07:** As a Publisher, I want a dashboard showing how many clicks my automated posts have generated today.
- **AC:** Given I am logged in, When I view my dashboard, Then I see a chart of total outbound clicks grouped by day.

## 3. Platform Admin

**US-08:** As an Admin, I want to view a dashboard of system health so I can ensure the platform is running smoothly.
- **AC:** Given the admin panel, Then I can see the number of deals ingested in the last hour and the error rate of the AI caption generation.

**US-09:** As an Admin, I want to manually flag a deal as "Expired".
- **AC:** Given a deal in the admin list, When I click 'Mark Expired', Then it is immediately hidden from the public feed and removed from publisher queues.

**US-10:** As an Admin, I want to manage Publisher subscriptions.
- **AC:** Given the publisher list, When I view a user, Then I can manually toggle them between 'Free' and 'Pro' status.
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
# Assumptions & Dependencies

**ID:** REQ-PRD-004
**Status:** Completed
**Last Updated:** 2026-06-29

## 1. Technical Dependencies
- **AI Models:** We depend on external APIs (e.g., OpenAI API or Anthropic) for generating social media captions. Uptime and cost are tied to these providers.
- **Social Platforms:** We assume the Telegram Bot API and Twitter API limits will remain sufficient for our planned volume. If API pricing or limits change drastically, the publishing engine may need refactoring.
- **Scraping targets:** The ingestion engine assumes the HTML structure of our 3 core eCommerce targets remains relatively stable.

## 2. Business Assumptions
- **Affiliate Compliance:** We assume that automatically applying affiliate tags to scraped content does not strictly violate the terms of our primary networks (e.g., Amazon Associates), provided we include proper disclosures on our platform.
- **Publisher Demand:** We assume Telegram admins are willing to provide their Bot Tokens to a third-party platform (LatestDeal) in exchange for automation.
# Browser & Device Support

**ID:** REQ-PRD-005
**Status:** Completed
**Last Updated:** 2026-06-29

## 1. Target Devices
- **Mobile First:** The public web frontend (LatestDeal.in) will be optimized primarily for mobile browsers, as the vast majority of traffic will originate from social media links.
- **Desktop:** The Admin and Publisher dashboards will be optimized for desktop/tablet use, as configuration and monitoring are complex tasks.
- **Native Apps:** iOS and Android native apps are explicitly out of scope for MVP.

## 2. Supported Browsers
The platform will support the latest 2 major versions of the following browsers:
- **Mobile:** Safari (iOS), Chrome for Android.
- **Desktop:** Google Chrome, Mozilla Firefox, Apple Safari, Microsoft Edge.
- **Legacy:** Internet Explorer 11 and older are explicitly NOT supported. Graceful degradation is not required for unsupported browsers.
# Compliance & Data Privacy

**ID:** REQ-PRD-006
**Status:** Completed
**Last Updated:** 2026-07-03

## 1. Scope of Tracking
The platform actively tracks outbound clicks to affiliate links for analytical purposes.
This includes capturing the `ip_address` and timestamps in the `clicks` table to prevent click fraud and provide publishers with CTR data.

## 2. GDPR & CCPA Compliance
- **Cookie Consent:** The public facing `LatestDeal.in` must display a standard cookie consent banner informing users that anonymous analytics and affiliate tracking cookies are in use.
- **Data Minimization:** IP addresses stored in the `clicks` table should be periodically anonymized (e.g., stripping the last octet) or hard-deleted after 90 days.
- **Publisher Data Rights:** Publishers have the right to request full deletion of their account, which must cascade to delete all associated API keys, Bot tokens, and Automation Rules.

## 3. Disclaimer Requirements
- All social media posts (where applicable) and the web platform must include a clear affiliate disclosure (e.g., "We may earn a commission if you purchase through our links") to comply with FTC and Amazon Associates guidelines.
