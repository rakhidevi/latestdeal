from worker.new.sdk.provider_sdk.base import (
    BaseProvider, BaseDiscoveryProvider, BaseExtractor, 
    BaseValidator, BasePublisher, ProviderHealth, PluginManifestV2
)
from worker.new.sdk.foundation.dto.models import SearchTargetDTO, UniversalProductDTO, CanonicalDealDTO, TraceContext
from typing import Dict, Any, List, Optional
from .manifest import get_amazon_manifest
from .capabilities import AmazonCapabilityDescriptor
from .validation.validator import AmazonValidator
from .discovery.query_builder import AmazonQueryBuilder
from .discovery.filters import BrandFilter, DiscountFilter, NodeFilter
from bs4 import BeautifulSoup
import re

class AmazonDiscoveryProvider(BaseDiscoveryProvider):
    def generate_urls(self, target: SearchTargetDTO) -> List[str]:
        # Example implementation translating target constraints into URLs
        builder = AmazonQueryBuilder()
        if target.constraints:
            if "brand" in target.constraints:
                builder.add_filter(BrandFilter(target.constraints["brand"]))
            if "min_discount" in target.constraints:
                builder.add_filter(DiscountFilter(target.constraints["min_discount"]))
            if "node" in target.constraints:
                builder.add_filter(NodeFilter(target.constraints["node"]))
                
        return [builder.build()]

class AmazonExtractor(BaseExtractor):
    def extract_product(self, raw_payload: Dict[str, Any], trace_context: TraceContext) -> Optional[UniversalProductDTO]:
        html = raw_payload.get('html', '')
        url = raw_payload.get('url', 'https://amazon.in/dp/UNKNOWN')
        soup = BeautifulSoup(html, 'html.parser')
        
        title = raw_payload.get('title')
        if not title:
            title_elem = soup.find('span', {'id': 'productTitle'})
            title = title_elem.text.strip() if title_elem else "Amazon Product"
        
        # Amazon ASIN
        asin = raw_payload.get('asin')
        if not asin:
            asin_match = re.search(r'/dp/([A-Z0-9]{10})', url)
            asin = asin_match.group(1) if asin_match else "UNKNOWN_ASIN"
            
        return UniversalProductDTO(
            provider="Amazon",
            provider_product_id=asin,
            url=url,
            title=title
        )
        
    def extract_deal(self, raw_payload: Dict[str, Any], product_uuid: str, trace_context: TraceContext) -> Optional[CanonicalDealDTO]:
        html = raw_payload.get('html', '')
        soup = BeautifulSoup(html, 'html.parser')
        
        # Price extraction (a-price-whole)
        price = raw_payload.get('price')
        mrp = raw_payload.get('mrp')
        
        if price is None or mrp is None:
            price_elem = soup.find('span', {'class': 'a-price-whole'})
            price_text = price_elem.text.replace(',', '') if price_elem else '0'
            
            mrp_elements = soup.find_all('span', {'class': 'a-text-price'})
            mrp_text = price_text
            for elem in mrp_elements:
                hidden = elem.find('span', {'aria-hidden': 'true'})
                if hidden and '₹' in hidden.text and '/' not in hidden.text:
                    mrp_text = hidden.text.replace('₹', '').replace(',', '')
                    break
            
            try:
                price = float(price_text)
                mrp = float(mrp_text)
            except ValueError:
                price = 0.0
                mrp = 0.0
                
        discount = 0.0
        if mrp > 0 and price < mrp:
            discount = ((mrp - price) / mrp) * 100.0
            
        availability = raw_payload.get('availability', True)
        seller = raw_payload.get('seller', 'unknown')
        return CanonicalDealDTO(
            universal_product_uuid=product_uuid,
            trace_context=trace_context,
            price=price,
            mrp=mrp,
            discount_percentage=discount,
            availability=availability,
            seller=seller,
            raw_payload=raw_payload,
            provider="Amazon"
        )
        
    def extract_grid(self, raw_payload: Dict[str, Any], target: SearchTargetDTO) -> List[UniversalProductDTO]:
        if target.expected_content in ["PRODUCT_DETAIL", "SEARCH_RESULT_GRID"]:
            return [UniversalProductDTO(
                provider="AmazonProvider", provider_product_id="B0MOCKDEFAULT",
                url=target.url, title=f"Shadow Product for {target.expected_content}"
            )]
        return []

class AmazonRoutingExtractor(BaseExtractor):
    def __init__(self):
        self._default = AmazonExtractor()
        
    def _get_delegate(self, target: SearchTargetDTO) -> BaseExtractor:
        if target and target.expected_content == "LIGHTNING_DEAL_GRID":
            from worker.new.providers.amazon.extractors.deals import AmazonDealsExtractor
            return AmazonDealsExtractor()
        elif target and target.expected_content == "COUPON_GRID":
            from worker.new.providers.amazon.extractors.coupons import AmazonCouponExtractor
            return AmazonCouponExtractor()
        elif target and target.expected_content == "BRAND_STORE_GRID":
            from worker.new.providers.amazon.extractors.brand import AmazonBrandExtractor
            return AmazonBrandExtractor()
        elif target and target.expected_content == "WAREHOUSE_GRID":
            from worker.new.providers.amazon.extractors.warehouse import AmazonWarehouseExtractor
            return AmazonWarehouseExtractor()
        elif target and target.expected_content == "BANK_OFFERS_GRID":
            from worker.new.providers.amazon.extractors.bank_offers import AmazonBankOfferExtractor
            return AmazonBankOfferExtractor()
        elif target and target.expected_content == "BEST_SELLERS_GRID":
            from worker.new.providers.amazon.extractors.best_sellers import AmazonBestSellerExtractor
            return AmazonBestSellerExtractor()
        elif target and target.expected_content == "NEW_RELEASES_GRID":
            from worker.new.providers.amazon.extractors.new_releases import AmazonNewReleaseExtractor
            return AmazonNewReleaseExtractor()
        elif target and target.expected_content == "TRENDING_GRID":
            from worker.new.providers.amazon.extractors.trending import AmazonTrendingExtractor
            return AmazonTrendingExtractor()
        elif target and target.expected_content == "SUBSCRIBE_GRID":
            from worker.new.providers.amazon.extractors.subscribe import AmazonSubscribeExtractor
            return AmazonSubscribeExtractor()
        elif target and target.expected_content == "BUNDLES_GRID":
            from worker.new.providers.amazon.extractors.bundle import AmazonBundleExtractor
            return AmazonBundleExtractor()
        elif target and target.expected_content == "EXCHANGE_GRID":
            from worker.new.providers.amazon.extractors.exchange import AmazonExchangeExtractor
            return AmazonExchangeExtractor()
        return self._default

    def extract_product(self, raw_payload: Dict[str, Any], trace_context: TraceContext) -> Optional[UniversalProductDTO]:
        return self._default.extract_product(raw_payload, trace_context)

    def extract_deal(self, raw_payload: Dict[str, Any], product_uuid: str, trace_context: TraceContext) -> Optional[CanonicalDealDTO]:
        return self._default.extract_deal(raw_payload, product_uuid, trace_context)

    def extract_grid(self, raw_payload: Dict[str, Any], target: SearchTargetDTO) -> List[UniversalProductDTO]:
        delegate = self._get_delegate(target)
        return delegate.extract_grid(raw_payload, target)

class AmazonPublisher(BasePublisher):
    def generate_affiliate_payload(self, deal: CanonicalDealDTO) -> Dict[str, Any]:
        import os
        import time
        from dotenv import load_dotenv
        
        # Load environment variables from backend .env if it exists
        project_root = os.path.dirname(os.path.dirname(os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))))
        env_path = os.path.join(project_root, 'backend', '.env')
        load_dotenv(env_path)
        
        use_shortlinks = os.environ.get("USE_SITESTRIPE_SHORTLINKS", "true").lower() == "true"
        
        if use_shortlinks:
            # SiteStripe UI Automation Fallback for `amzn.to` or `link.amazon` shortlinks
            from playwright.sync_api import sync_playwright
            from playwright_stealth import Stealth
            
            short_url = ""
            user_data_dir = os.path.join(project_root, 'worker', 'browser_profile')
            
            with sync_playwright() as p:
                context = None
                for attempt in range(2):
                    try:
                        context = p.chromium.launch_persistent_context(
                            user_data_dir=user_data_dir,
                            headless=False,
                            executable_path=r"C:\Program Files\Google\Chrome\Application\chrome.exe",
                            permissions=["clipboard-read", "clipboard-write"],
                            args=["--disable-blink-features=AutomationControlled"]
                        )
                        break
                    except Exception as e:
                        if attempt == 0:
                            import subprocess, sys
                            subprocess.run([sys.executable, os.path.join(project_root, "worker", "kill_zombie_chrome.py")])
                            time.sleep(2)
                        else:
                            raise e
                
                try:
                    page = context.pages[0] if context.pages else context.new_page()
                    Stealth().use_sync(page)
                    
                    # Navigate to the raw ASIN
                    asin = deal.universal_product_uuid
                    raw_url = f"https://www.amazon.in/dp/{asin}"
                    page.goto(raw_url, wait_until="domcontentloaded", timeout=60000)
                    
                    try:
                        # New SiteStripe design (2025+):
                        # A gold "Get Link." button opens a popover with Short Link / Full Link radio
                        # Step 1: Wait for the SiteStripe bar to load
                        page.wait_for_selector("#amzn-ss-wrap", timeout=15000)
                        time.sleep(1.5)
                        
                        import pyperclip
                        pyperclip.copy("") # Clear clipboard first
                        
                        # Step 2: Click the "Get Link." button to open the popover
                        # Try multiple possible selectors for the Get Link button
                        get_link_selectors = [
                            "#amzn-ss-getlink-btn",
                            ".amzn-ss-getlink-btn",
                            "button.amzn-ss-get-link-btn",
                            "[data-action='amzn-ss-get-link']",
                        ]
                        clicked = False
                        for sel in get_link_selectors:
                            try:
                                btn = page.locator(sel).first
                                if btn.count() > 0:
                                    btn.click(force=True)
                                    clicked = True
                                    break
                            except:
                                continue
                        
                        if not clicked:
                            # Fallback: find any button/link with "Get Link" text in the SiteStripe bar
                            page.evaluate("""() => {
                                let wrap = document.getElementById('amzn-ss-wrap');
                                if (wrap) {
                                    let btns = wrap.querySelectorAll('button, a, [role="button"]');
                                    for (let b of btns) {
                                        if (b.innerText && b.innerText.toLowerCase().includes('get link')) {
                                            b.click(); return true;
                                        }
                                    }
                                }
                                return false;
                            }""")
                        
                        time.sleep(1.5)  # Wait for popover to animate open
                        
                        # Step 3: The "Short Link" radio is selected by default.
                        # Click the "Copy affiliate link" button
                        page.wait_for_selector("#amzn-ss-copy-affiliate-link-btn-announce", timeout=8000)
                        copy_btn = page.locator("#amzn-ss-copy-affiliate-link-btn-announce").first
                        copy_btn.click(force=True)
                        
                        # Step 4: Wait for "Copied to clipboard" toast to confirm success
                        try:
                            page.wait_for_selector(
                                "#amzn-ss-copy-toast:not([style*='display: none'])", 
                                timeout=5000
                            )
                        except:
                            pass
                        
                        time.sleep(1.0)
                        
                        try:
                            short_url = page.evaluate("navigator.clipboard.readText()")
                        except:
                            short_url = ""
                            
                        if not short_url or ("amzn.to" not in short_url and "link.amazon" not in short_url):
                            short_url = pyperclip.paste()
                        
                    except Exception as e:
                        print(f"SiteStripe automation failed: {e}")
                finally:
                    if context:
                        context.close()
                        
            if short_url and ("amzn.to" in short_url or "link.amazon" in short_url):
                return {"affiliate_url": short_url}
        
        # Fallback to Algorithmic Generation (Fast, Crash-Free, 100% Tracking Reliable)
        affiliate_tag = os.environ.get("AMAZON_AFFILIATE_TAG", "kridaymart-21")
        asin = deal.universal_product_uuid
        
        import re
        raw_title = deal.raw_payload.get('title', 'product')
        slug = re.sub(r'[^a-zA-Z0-9\s-]', '', raw_title).strip()
        slug = re.sub(r'[\s-]+', '-', slug)
        slug = slug[:100].strip('-') or "dp"
        
        authentic_link = f"https://www.amazon.in/{slug}/dp/{asin}?tag={affiliate_tag}&linkCode=ll2&ref_=as_li_ss_tl"
        return {"affiliate_url": authentic_link}

class AmazonHealthTracker(ProviderHealth):
    def __init__(self):
        self.successes = 0
        self.failures = 0
        
    def record_success(self) -> None:
        self.successes += 1
        
    def record_failure(self, reason: str) -> None:
        self.failures += 1
        
    def get_health_score(self) -> float:
        total = self.successes + self.failures
        if total == 0: return 100.0
        return (self.successes / total) * 100.0

class AmazonProvider(BaseProvider):
    """The canonical Amazon Reference Provider v1.0."""
    
    def __init__(self):
        self._manifest = get_amazon_manifest()
        self._discovery = AmazonDiscoveryProvider()
        self._extractor = AmazonRoutingExtractor()
        self._validator = AmazonValidator()
        self._publisher = AmazonPublisher()
        self._health = AmazonHealthTracker()
        
    def get_manifest(self) -> PluginManifestV2:
        return self._manifest
        
    def get_discovery_provider(self) -> BaseDiscoveryProvider:
        return self._discovery
        
    def get_extractor(self) -> BaseExtractor:
        return self._extractor
        
    def get_validator(self) -> BaseValidator:
        return self._validator
        
    def get_publisher(self) -> BasePublisher:
        return self._publisher
        
    def get_health_tracker(self) -> ProviderHealth:
        return self._health
