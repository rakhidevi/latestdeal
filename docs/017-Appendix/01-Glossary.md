# Glossary

**ID:** REQ-APP-001
**Status:** Draft
**Last Updated:** 2026-06-29

## Definitions

- **Deal:** A discrete data object representing a discounted product, containing price, original price, URL, image, and timestamps.
- **Affiliate Tag:** A unique identifier appended to a URL used to track the source of traffic for commission attribution.
- **Publisher:** A user of LatestDeal who utilizes the platform's tools to distribute deals to their own audience (e.g., a Telegram channel admin).
- **Ingestion Engine:** The backend service responsible for fetching, parsing, and normalizing raw deal data from external sources.
- **Publishing Engine:** The service that formats deals (text + images) and pushes them to external APIs (Telegram, Twitter, Discord).
- **Composite Image:** A dynamically generated image combining product photos, pricing text, and branding overlays created via the Image Generation Engine.
