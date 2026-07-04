# Data Models & Schema

**ID:** REQ-SRS-003
**Status:** Completed
**Last Updated:** 2026-06-29

## 1. Users Table
Handles authenticating Admins and Publishers.
- `id` (PK)
- `name`, `email`, `password`
- `role` (enum: 'admin', 'publisher')
- `subscription_tier` (enum: 'free', 'pro')

## 2. Deals Table
The core entity storing deal information.
- `id` (PK)
- `title`, `description`, `category_id` (FK)
- `original_price` (decimal), `discounted_price` (decimal)
- `coupon_code` (String - nullable)
- `merchant_id` (FK)
- `original_url` (Text)
- `image_url` (Text - Path to generated composite image)
- `status` (enum: 'active', 'expired')
- `expires_at` (Timestamp)

## 3. Publisher_Integrations Table
Stores the API keys/tokens for publishers to automate their social channels.
- `id` (PK)
- `user_id` (FK)
- `platform` (enum: 'telegram', 'twitter', 'whatsapp', 'facebook', 'instagram', 'x')
- `bot_token` (Encrypted String)
- `chat_id` (String)
- `affiliate_tag` (String - The publisher's unique ID, e.g., Amazon Associate ID)
- `is_active` (Boolean)

## 4. Automation_Rules Table
Stores the filters publishers apply to their automated feeds.
- `id` (PK)
- `integration_id` (FK)
- `category_id` (FK - nullable, if they only want specific categories)
- `min_discount_percentage` (Integer)
- `status` (enum: 'active', 'paused')

## 5. Merchants Table
Stores supported platforms and specific affiliate structures.
- `id` (PK)
- `name`, `domain`
- `store_id` (String - The platform store identifier, e.g., kridaymart-21)
- `affiliate_param_key` (String - e.g., 'tag', 'aff_id')

## 6. Clicks Table
Tracks outbound analytics for end-to-end tracking.
- `id` (PK)
- `deal_id` (FK)
- `publisher_integration_id` (FK - nullable)
- `ip_address` (String)
- `created_at` (Timestamp)

## 7. Subscribers Table
Stores contact information for shopper retention.
- `id` (PK)
- `email` (String - nullable)
- `push_token` (String - nullable)
- `status` (enum: 'active', 'unsubscribed')

## 8. Price_Alerts Table
Stores user-requested price alerts for specific keywords.
- `id` (PK)
- `subscriber_id` (FK)
- `keyword` (String)
- `target_price` (decimal)
- `status` (enum: 'active', 'fulfilled')

## 9. Price_History Table
Logs price fluctuations for active deals over time.
- `id` (PK)
- `deal_id` (FK)
- `price` (decimal)
- `recorded_at` (Timestamp)
