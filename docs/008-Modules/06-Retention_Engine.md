# Shopper Retention Engine

**ID:** REQ-MOD-006
**Status:** Completed
**Last Updated:** 2026-07-03

## Responsibility
Engage and retain organic website visitors by allowing them to subscribe to deal alerts without requiring a full user account or joining a social media group.

## Implementation Details
- **Web Push Notifications:** Integrates with OneSignal (Free Tier) to prompt users to "Allow Notifications" when they visit the site. Critical/Flash deals trigger an API call to OneSignal to push alerts directly to their browser/mobile lock screen.
- **Email Newsletter Capture:** A simple frontend form (in the footer or a timed modal) that captures the user's email address.
- **Price Alerts (Targeted Notifications):** Users can subscribe to specific keywords (e.g., "iPhone") and target prices. When the Local Worker ingests a new deal, the server checks the `price_alerts` table. If a match is found and the price is below the target, a targeted push/email is dispatched and the alert is marked `fulfilled`.
- **Data Storage:** Emails and push subscription preferences are stored locally in the `subscribers` table to avoid paid CRM costs (like Mailchimp).
- **Campaign Execution:** Admins can export the `subscribers` table to CSV or use Laravel's built-in mailer (`Mail::queue()`) to send a weekly digest of top deals.
