# Authentication & User Management Module

**ID:** REQ-MOD-003
**Status:** Completed
**Last Updated:** 2026-06-29

## Responsibility
Handle login, registration, password resets, and role management (Admin vs Publisher).

## Implementation Details
- Uses **Laravel Breeze** or standard Laravel Auth scaffolding.
- **Roles:** Handled via a simple `role` column on the `users` table. No complex Spatie/Permission packages needed for MVP.
- **API Tokens:** Publishers can generate API tokens from their dashboard via **Laravel Sanctum** to integrate their own custom scripts if desired.
