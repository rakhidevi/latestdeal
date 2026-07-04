# ADR: Use PHP & Laravel

**ID:** REQ-ADR-001
**Status:** Completed
**Date:** 2026-06-29

## Context
We need a robust backend framework to handle API ingestion, database management, and background queues. The primary constraint is that the hosting environment is MilesWeb Shared/cPanel PHP hosting.

## Decision
We will use PHP 8.2+ with the Laravel 11.x framework.

## Consequences
- **Positive:** Laravel provides built-in routing, ORM, and queue management that works perfectly on standard PHP environments. Highly performant for MVP.
- **Negative:** We cannot use long-running daemon processes like WebSockets natively without external services (e.g., Pusher), which is acceptable for this MVP.
