# API Design Guidelines

**ID:** REQ-SRS-004
**Status:** Completed
**Last Updated:** 2026-06-29

## 1. RESTful Principles
All external APIs (e.g., for ingesting deals from partners or future mobile apps) will follow standard RESTful conventions.
- **Format:** JSON exclusively for requests and responses.
- **Versioning:** API versioning via URL path (e.g., `/api/v1/deals`).

## 2. Authentication
- **Web App:** Standard Session-based authentication for Admins and Publishers logging into the dashboard.
- **API Access:** **Laravel Sanctum** will be used to issue API tokens for external ingestion scripts or "Pro" publishers wanting programmatic access.

## 3. Standard Response Format
All API responses will follow a predictable envelope:
```json
{
  "success": true,
  "data": { ... },
  "message": "Optional success/error message"
}
```

## 4. Rate Limiting
- Public endpoints will be throttled heavily (e.g., 60 requests per minute) using Laravel's built-in rate limiter to prevent abuse on shared hosting resources.
