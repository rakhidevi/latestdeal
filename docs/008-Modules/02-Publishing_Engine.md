# Publishing Engine Module

**ID:** REQ-MOD-002
**Status:** Completed
**Last Updated:** 2026-06-29

## Responsibility
Format approved deals, apply affiliate tags, and push to external platforms.

## Implementation Details
- **Queue Worker:** Runs as a background job for automated platforms (`PublishDealToTelegramJob`, `PublishDealToXJob`).
- **Tag Replacement:** Uses PHP's native URL parsing functions to safely rebuild query strings and append the Publisher's specific `storeId` or tag.
- **Supported Automated Platforms:** Telegram (Channels/Groups) via Bot API, X.com via API, Facebook Pages/Instagram via Graph API.
- **Click-to-Share (Manual):** For platforms like WhatsApp and Snapchat where automated bot posting is not 100% free or violates terms, the dashboard provides a "1-Click Share Intent" (e.g., `wa.me` links) with the pre-formatted customized message and tracked affiliate link.
- **Rate Limiting:** Must respect API rate limits implemented via Laravel's Redis/Cache throttle lock.
