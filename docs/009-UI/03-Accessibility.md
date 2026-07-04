# Accessibility (a11y) Standards

**ID:** REQ-UI-003
**Status:** Completed
**Last Updated:** 2026-06-29

## Guidelines
- **Color Contrast:** All text must meet WCAG 2.1 AA contrast ratios (especially the white text on red discount badges).
- **Alt Text:** All product images must have `<img alt="{{ $deal->title }}">`.
- **Keyboard Navigation:** The public feed must be fully navigable via the `Tab` key, with visible focus states on all deal links.
