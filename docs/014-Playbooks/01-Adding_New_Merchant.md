# Playbook: Adding a New Merchant

**ID:** REQ-PBK-001
**Status:** Completed
**Last Updated:** 2026-06-29

## Steps to add a new eCommerce scraping target:
1. **Analyze DOM:** Inspect the target's product page to identify CSS selectors for Title, Price, Image, and Availability.
2. **Create Scraper Class:** Implement the `ScraperInterface` in `App\Services\Scrapers\NewMerchantScraper.php`.
3. **Register URL Pattern:** Add the domain to the routing dictionary so the ingestion engine knows which scraper to use when given a raw URL.
4. **Test Affiliate Params:** Verify what URL parameters the merchant requires for affiliate attribution (e.g., `?tag=...` vs `?aff_id=...`).
