# Admin Dashboard Module

**ID:** REQ-MOD-004
**Status:** Completed
**Last Updated:** 2026-06-29

## Responsibility
Provide a central hub for Platform Admins to manage the system.

## Implementation Details
- **Deals View:** List all deals, with filters for active/expired. Ability to manually expire deals.
- **Merchant Management:** Manage supported eCommerce platforms and store specific IDs (e.g., `StoreID: kridaymart-21`). Configure specific affiliate URL structures for each.
- **Affiliate Link Generator:** A manual UI tool for admins to instantly generate tracked affiliate links for custom campaigns, utilizing the saved `storeIds`.
- **End-to-End Tracking Dashboard:** Visualize outbound clicks, group by publisher integration, and track overall platform CTRs (Click-Through Rates).
- **Users View:** List all publishers, toggle Pro status, or ban abusive users.
- **System View:** Display queue backlog size (e.g., `Job::count()`) and recent API ingestion errors.
- **UI Tool:** Built using standard Blade templates (or optionally FilamentPHP if rapid admin generation is desired).
