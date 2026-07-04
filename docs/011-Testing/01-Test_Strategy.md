# Test Strategy

**ID:** REQ-TST-001
**Status:** Completed
**Last Updated:** 2026-06-29

## 1. Unit Testing (PHPUnit)
- Focus on critical business logic inside the Models and Services.
- **Specifically:** Test the Regex that replaces affiliate tags to ensure we never accidentally leak one publisher's tag to another.

## 2. Feature Testing
- Test the API endpoints using Laravel's built-in HTTP testing (`$this->postJson(...)`).
- Verify that standard endpoints return `200 OK` and correct JSON structures.

## 3. End-to-End (E2E) Testing
- Use **Laravel Dusk** for automated browser testing.
- Ensure the Publisher Dashboard flows (e.g., entering API keys, clicking the WhatsApp share button, generating affiliate links) work perfectly from a UI perspective.

## 4. Manual QA
- Before major releases, verify the Telegram integration manually to ensure rate limits aren't triggered.
