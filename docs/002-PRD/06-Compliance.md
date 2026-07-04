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
