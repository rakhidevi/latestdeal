# Event-Driven Architecture

## Event Bus Strategy
LatestDeal uses **Laravel Database Queues** to handle all asynchronous events. This is optimized for shared hosting constraints while providing a seamless upgrade path to Redis, RabbitMQ, or SQS in the future.

## Standardized Event Payload
Every event must adhere to the following schema to ensure traceability:

```json
{
  "event_id": "uuid",
  "deal_id": 12345,
  "provider": "amazon",
  "timestamp": "2026-07-19T08:00:00Z",
  "correlation_id": "trace-uuid-1234",
  "payload_version": "1.0",
  "metadata": {
    "price": 499.00,
    "discount_pct": 50
  }
}
```
*Note: The `correlation_id` is critical. It is generated when a deal is first discovered and passed through every subsequent event, allowing full lifecycle tracing.*

## Event Lifecycle
The pipeline is strictly granular and independent:
1. `DealDiscovered` (Fired by API ingestion controller)
2. `DealNormalized`
3. `DealValidated`
4. `DealScored` (Triggers AI scoring)
5. `DealClassified` (Assigns categories/tags)
6. `DealCompared` (Triggers real-time price comparison)
7. `DealPublished`
8. `DealIndexed`
9. `DealShared` (Triggers Telegram/WhatsApp publishing)
10. `DealTracked`

## The `DealUpdated` Event
Not all updates are price changes. The `DealUpdated` event is fired for:
- Coupon changes
- Title changes
- Image URL changes
- Stock availability changes
- Delivery changes

## Deal Events Table
Every event lifecycle is persisted to the `deal_events` database table:
`| deal_id | event | timestamp | correlation_id |`
