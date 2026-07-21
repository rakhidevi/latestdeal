# Monitoring & Observability

## First-Class Monitoring
Monitoring is a core primitive, not an afterthought. The system tracks metrics beyond simple uptime.

## Core Metrics Tracked
- Deals discovered/hour
- Deals published/hour
- Deals rejected (with rejection reason breakdown)
- Average scrape latency
- Queue size (backlog)
- AI response time
- Browser startup time
- Provider uptime
- API latency
- Database write latency
- Event processing time
- Failed jobs
- Duplicate rate
- Price-change detection rate

## Provider Health Dashboard
Every provider implements a `.health()` method exposing:
`OK -> Selectors Valid -> Browser Running -> API Reachable -> Cookies Valid`

If a DOM change breaks parsing, the dashboard immediately isolates the failure to "Selectors Valid: False" for that specific provider version.

## Structure
```
worker/new/monitoring/
  health.py
  metrics.py
  heartbeat.py
  alerts.py
  logging.py
  dashboard.py
```
