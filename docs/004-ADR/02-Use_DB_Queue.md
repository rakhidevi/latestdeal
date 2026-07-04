# ADR: Use Database Queue

**ID:** REQ-ADR-002
**Status:** Completed
**Date:** 2026-06-29

## Context
Background tasks (scraping, pinging Telegram APIs) cannot block the main web thread. Standard shared hosting does not support Redis or Supervisor natively.

## Decision
We will use Laravel's Database Queue Driver triggered by a scheduled cron job every minute.

## Consequences
- **Positive:** Zero extra infrastructure cost. Works on any cPanel hosting.
- **Negative:** Slightly higher database CPU usage compared to Redis. Maximum granularity for job execution is 1 minute (cron limit), which is fast enough for deals.
