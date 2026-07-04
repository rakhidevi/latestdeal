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
