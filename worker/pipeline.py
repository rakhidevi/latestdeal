from models import Deal, ScraperException, AICategoryFailed, DealCategory
from url_resolver import resolve_url
from merchant_registry import MerchantDetector, get_scraper
from ai_enricher import enrich_deal
from observability import MetricsTracker
import traceback

class ScrapingPipeline:
    
    @classmethod
    def process_url(cls, raw_url: str, source: str = "telegram") -> Deal:
        tracker = MetricsTracker()
        merchant = "unknown"
        try:
            # 1. URL Resolution
            tracker.mark_start("resolve")
            print(f"Resolving URL: {raw_url}")
            canonical_url = resolve_url(raw_url)
            tracker.mark_end("resolve")
            
            # 2. Merchant Detection
            merchant = MerchantDetector.detect(canonical_url)
            print(f"Detected Merchant: {merchant}")
            
            # 3. Get Scraper from Registry
            scraper = get_scraper(merchant)
            
            # 4. Scrape Product Data
            tracker.mark_start("scrape")
            deal = scraper.extract(canonical_url)
            deal.source = source
            
            # 5. Generate Affiliate Link
            try:
                affiliate_url = scraper.generate_affiliate(deal)
                deal.affiliate_url = affiliate_url
            except Exception as e:
                print(f"Warning: Failed to generate affiliate link: {e}")
                deal.affiliate_url = canonical_url # Fallback to raw canonical if affiliate fails
            tracker.mark_end("scrape")
                
            # 6. AI Enrichment (Category, Score, Caption)
            tracker.mark_start("ai")
            try:
                deal = enrich_deal(deal)
            except Exception as e:
                print(f"AI Enrichment failed: {e}")
            tracker.mark_end("ai")
                
            # 7. Strict Deal Validation (Reject "nodeal")
            if not deal.title:
                raise ScraperException("Deal rejected: No product title extracted (nodeal).")
                
            if deal.category and deal.category.name.lower() in ["nodeal", "no deal", "not a deal"]:
                raise ScraperException("Deal rejected: AI classified this as 'nodeal'.")
                
            tracker.record_success(merchant)
            MetricsTracker.print_summary()
            return deal
            
        except ScraperException as se:
            print(f"Pipeline failed with known structured error: {str(se)}")
            tracker.record_failure(merchant, se.__class__.__name__)
            MetricsTracker.print_summary()
            raise
        except Exception as e:
            traceback.print_exc()
            tracker.record_failure(merchant, "UnknownError")
            MetricsTracker.print_summary()
            raise ScraperException(f"Pipeline failed unexpectedly: {str(e)}")
