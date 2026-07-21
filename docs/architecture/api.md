# API & Ingestion Pipeline

## Ultra-Thin Controller Principle
The Laravel backend acts strictly as an entry point. It must never perform synchronous processing, HTTP requests, or AI scoring during the ingestion HTTP request.

### The Pipeline:
1. `Validate Request`: Ensure payload matches schema.
2. `Persist Raw Payload`: Save data to `deals` table (status: pending).
3. `Return HTTP 200`: Immediately release the Python worker connection.
4. `Queue Processing`: Dispatch `DealDiscovered` event to background queues.

## Feature Flags
Features are controlled via environment/configuration flags to allow zero-downtime toggles:
```env
AMAZON_ENABLED=true
FLIPKART_ENABLED=false
AI_SCORING_ENABLED=true
TELEGRAM_ENABLED=true
PRICE_COMPARE_ENABLED=false
```
