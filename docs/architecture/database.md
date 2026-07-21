# Database Architecture

## Deal Tracking
All deals are stored in the core `deals` table. Updates to deals must not mutate historical context blindly; instead, they fire `PriceChanged` or `DealUpdated` events.

## Event Tracking (`deal_events`)
To facilitate deep debugging and audit trails, every step in a deal's lifecycle is logged.
```sql
CREATE TABLE deal_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    deal_id BIGINT UNSIGNED NOT NULL,
    event VARCHAR(255) NOT NULL,
    correlation_id VARCHAR(36) NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(deal_id),
    INDEX(correlation_id)
);
```

## Migration Strategy
Database migrations must remain backwards-compatible during the Strangler Fig migration to ensure legacy workers can still operate safely.
