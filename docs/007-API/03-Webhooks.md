# Webhook Architecture (Telegram)

**ID:** REQ-API-003
**Status:** Completed
**Last Updated:** 2026-06-29

## Context
When publishers interact with the LatestDeal Master Telegram Bot (e.g., sending `/start` to get their `chat_id` for configuration), the system needs to receive these messages.

## Decision: Webhooks over Polling
Because we are on a standard PHP web server without long-running daemons, we CANNOT use `getUpdates` long-polling. 

We must register a secure Webhook with Telegram:
`POST https://api.telegram.org/bot<token>/setWebhook?url=https://latestdeal.in/api/v1/webhooks/telegram`

## Flow
1. Publisher messages the Bot on Telegram.
2. Telegram sends a POST request to our `/api/v1/webhooks/telegram` route.
3. Laravel Controller parses the incoming JSON, extracts the `chat_id`, and saves it to the database or replies to the user.
