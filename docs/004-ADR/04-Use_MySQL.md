# ADR: Use MySQL

**ID:** REQ-ADR-004
**Status:** Completed
**Date:** 2026-06-29

## Context
We need a database to store deals, users, and publisher configurations.

## Decision
We will use MySQL 8.x (or MariaDB equivalent), which is the standard relational database provided by MilesWeb cPanel hosting.

## Consequences
- **Positive:** 100% compatibility with hosting constraints. Excellent support in Laravel (Eloquent). Strong data integrity for financial/affiliate data.
- **Negative:** Schema migrations required for changes (unlike NoSQL), which is standard practice anyway.
