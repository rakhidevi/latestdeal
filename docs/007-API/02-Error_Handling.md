# Error Handling Standards

**ID:** REQ-API-002
**Status:** Completed
**Last Updated:** 2026-06-29

## Standard Error Response
All API errors must return a consistent JSON structure to clients.

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_FAILED",
    "message": "The provided deal data is invalid.",
    "details": {
      "price": ["The price must be a valid decimal."]
    }
  }
}
```

## HTTP Status Codes
- `200 OK`: Success.
- `201 Created`: Resource created (e.g., deal ingested).
- `401 Unauthorized`: Missing or invalid API token.
- `403 Forbidden`: Token valid, but lacks permissions.
- `422 Unprocessable Entity`: Validation failure.
- `429 Too Many Requests`: Rate limit exceeded.
