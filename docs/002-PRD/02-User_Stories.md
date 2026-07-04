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
