# Indexing Strategy

**ID:** REQ-DB-003
**Status:** Completed
**Last Updated:** 2026-06-29

## Critical Indexes for Performance
Because we are heavily caching, DB reads are minimized. However, background jobs will query heavily.

### `deals` table
- `INDEX(status, category_id, discounted_price)` - For the publishing engine checking rules.
- `INDEX(created_at)` - For chronological feed sorting.
- `UNIQUE(url)` - To prevent duplicate ingestion of the exact same deal.

### `jobs` table
- Laravel handles this natively, but `INDEX(queue, reserved_at)` is critical for the queue worker to pop jobs efficiently.
