# Schema Definitions (Detailed)

**ID:** REQ-DB-001
**Status:** Completed
**Last Updated:** 2026-06-29

## Critical Tables

### `deals`
- `id` (bigint, unsigned, auto_increment)
- `category_id` (bigint, unsigned, index)
- `merchant_id` (bigint, unsigned, index)
- `title` (varchar 255)
- `original_price` (decimal 10,2)
- `discounted_price` (decimal 10,2)
- `coupon_code` (varchar 50, nullable)
- `url` (text)
- `image_path` (varchar 255)
- `status` (enum: 'active', 'expired')
- `created_at`, `updated_at`

### `publisher_integrations`
- `id` (bigint, unsigned, auto_increment)
- `user_id` (bigint, unsigned, index)
- `platform` (varchar 50) - e.g., 'telegram'
- `bot_token` (text, encrypted)
- `chat_id` (varchar 255)
- `affiliate_tag` (varchar 255)

### `jobs` & `failed_jobs`
- Standard Laravel tables for managing the database queue.

### `merchants`
- `id` (bigint, unsigned, auto_increment)
- `name` (varchar 255) - e.g., 'KridayMart'
- `domain` (varchar 255)
- `store_id` (varchar 255) - e.g., 'kridaymart-21'
- `affiliate_param_key` (varchar 50) - e.g., 'tag', 'aff_id'

### `clicks`
- `id` (bigint, unsigned, auto_increment)
- `deal_id` (bigint, unsigned, index)
- `publisher_integration_id` (bigint, unsigned, index, nullable)
- `ip_address` (varchar 45)
- `created_at` (timestamp)

### `subscribers`
- `id` (bigint, unsigned, auto_increment)
- `email` (varchar 255, unique, nullable)
- `push_token` (varchar 255, unique, nullable)
- `status` (enum: 'active', 'unsubscribed')
- `created_at`, `updated_at`

### `price_alerts`
- `id` (bigint, unsigned, auto_increment)
- `subscriber_id` (bigint, unsigned, index)
- `keyword` (varchar 255)
- `target_price` (decimal 10,2)
- `status` (enum: 'active', 'fulfilled')
- `created_at`, `updated_at`

### `price_history`
- `id` (bigint, unsigned, auto_increment)
- `deal_id` (bigint, unsigned, index)
- `price` (decimal 10,2)
- `recorded_at` (timestamp)
