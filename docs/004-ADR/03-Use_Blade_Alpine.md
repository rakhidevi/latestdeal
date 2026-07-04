# ADR: Use Blade & Alpine.js

**ID:** REQ-ADR-003
**Status:** Completed
**Date:** 2026-06-29

## Context
We need a frontend stack that is fast, SEO-friendly, and compatible with shared PHP hosting (no Node.js required on the server).

## Decision
We will use Laravel Blade for server-side rendering, styled with Tailwind CSS, and Alpine.js for lightweight frontend interactivity.

## Consequences
- **Positive:** Maximum SEO performance, zero client-side hydration cost, perfectly runs on standard PHP hosting.
- **Negative:** Not a Single Page Application (SPA), so full page reloads occur between distinct views (mitigated by Turbolinks/Livewire if needed later).
