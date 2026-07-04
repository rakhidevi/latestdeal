# SEO Engine

**ID:** REQ-MOD-007
**Status:** Completed
**Last Updated:** 2026-07-03

## Responsibility
Ensure rapid indexing and high organic search visibility by providing structured data to search engines for all active deals.

## Implementation Details
- **Structured Data (JSON-LD):** The backend dynamically injects `Product` and `Offer` schema into the `<head>` of every deal page. This includes the `name`, `image`, `price`, `priceCurrency`, and aggregated `review` ratings, allowing Google to display rich snippets (prices/stars) directly in search results.
- **Dynamic XML Sitemap:** A scheduled cron job (or real-time event listener) maintains an updated `sitemap.xml`. Only `active` deals are included. Expired deals are removed from the sitemap to optimize crawl budget.
- **Index Pinging:** Upon ingestion of a high-value or "Flash" deal, the system uses the Google Indexing API to request an immediate crawl, ensuring the deal appears in search results before it expires.
- **Canonical URLs:** Ensure that duplicate deals (or deals accessed via different tracking parameters) all point back to a single canonical URL to prevent SEO penalty for duplicate content.
