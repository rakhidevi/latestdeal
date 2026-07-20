import time
import random
from playwright.sync_api import sync_playwright
from playwright_stealth import Stealth

from interfaces import MerchantScraper
from models import Deal, PlaywrightTimeout
from browser_utils import setup_browser_stateless, get_page

DESKTOP_USER_AGENTS = [
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
    "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
]

class GenericScraper(MerchantScraper):
    """Fallback scraper for unsupported merchants (e.g. Flipkart, Ajio, etc) using Generic DOM extraction."""
    @classmethod
    def can_handle(cls, domain: str) -> bool:
        return True

    def extract(self, url: str) -> Deal:
        with sync_playwright() as p:
            browser, context = setup_browser_stateless(p)
            try:
                page = get_page(context)
                Stealth().use_sync(page)
                
                ua = random.choice(DESKTOP_USER_AGENTS)
                page.set_extra_http_headers({"User-Agent": ua})
                
                try:
                    page.goto(url, wait_until="domcontentloaded", timeout=60000)
                    time.sleep(random.uniform(5.0, 10.0))
                    
                    # Generic extraction
                    title = page.title()
                    
                    if "Robot Check" in title or "CAPTCHA" in title or "Bot Check" in title:
                        raise ScraperException("needs_desktop_processing")
                        
                    if "Page Not Found" in title or "404" in title or not title:
                        raise ScraperException("Deal rejected: Page Not Found or Empty Title (nodeal)")
                        
                    image_url = ""
                    meta_img = page.locator("meta[property='og:image']").first
                    if meta_img.count() > 0:
                        image_url = meta_img.get_attribute("content") or ""
                        
                    return Deal(
                        merchant="unknown",
                        title=title,
                        image_url=image_url,
                        canonical_url=url,
                    )
                except Exception as e:
                    raise PlaywrightTimeout(f"Generic extraction failed: {str(e)}")
            finally:
                context.close()
                browser.close()

    def generate_affiliate(self, deal: Deal) -> str:
        return deal.canonical_url


class FlipkartScraper(GenericScraper):
    @classmethod
    def can_handle(cls, domain: str) -> bool:
        return "flipkart" in domain
        
    def extract(self, url: str) -> Deal:
        deal = super().extract(url)
        deal.merchant = "flipkart"
        return deal


class UdemyScraper(MerchantScraper):
    @classmethod
    def can_handle(cls, domain: str) -> bool:
        return "udemy" in domain

    def extract(self, url: str) -> Deal:
        with sync_playwright() as p:
            browser, context = setup_browser_stateless(p)
            try:
                page = get_page(context)
                Stealth().use_sync(page)
                
                try:
                    ua = random.choice(DESKTOP_USER_AGENTS)
                    page.set_extra_http_headers({"User-Agent": ua})
                    page.goto(url, wait_until="domcontentloaded", timeout=60000)
                    time.sleep(5)
                    
                    title_element = page.locator("h1[data-purpose='lead-title']").first
                    title = title_element.inner_text().strip() if title_element.count() > 0 else page.title()
                    
                    if "Robot Check" in title or "CAPTCHA" in title or "Bot Check" in title or "Attention Required" in title:
                        raise ScraperException("needs_desktop_processing")
                        
                    if "Page Not Found" in title or "404" in title or not title:
                        raise ScraperException("Deal rejected: Page Not Found or Empty Title (nodeal)")
                    
                    original_price_html = ""
                    el = page.locator("div[data-purpose='discount-price'] s span").first
                    if el.count() > 0:
                        original_price_html = el.inner_text().strip()
                            
                    image_url = ""
                    img_element = page.locator("img[data-purpose='course-image']").first
                    if img_element.count() > 0:
                        image_url = img_element.get_attribute("src") or ""
                        
                    # Clean price to float
                    def clean_price(p_str):
                        if not p_str: return None
                        c = ''.join(c for c in p_str if c.isdigit() or c == '.')
                        try: return float(c) if c else None
                        except: return None
                        
                    orig_price = clean_price(original_price_html)
                    
                    return Deal(
                        merchant="udemy",
                        title=title,
                        price=0.0, # Udemy deals posted are usually 100% off
                        original_price=orig_price,
                        discount_percent=100.0 if orig_price else None,
                        image_url=image_url,
                        canonical_url=url
                    )
                except Exception as e:
                    raise PlaywrightTimeout(f"Udemy extraction failed: {str(e)}")
            finally:
                context.close()
                browser.close()

    def generate_affiliate(self, deal: Deal) -> str:
        # We handle Udemy affiliation via impact URL parameters (carried over from telegram_scraper logic)
        import urllib.parse
        try:
            parsed = urllib.parse.urlparse(deal.canonical_url)
            query_params = urllib.parse.parse_qs(parsed.query)
            
            impact_params = {
                'im_ref': ['3UDwqRybsxyZWDu1FDz21SsZUkuVtBwg7TRYz00'],
                'irpid': ['7475040'],
                'utm_medium': ['affiliate'],
                'utm_source': ['impact'],
                'utm_audience': ['mx'],
                'utm_tactic': ['"APAC","Coupon/Deal"'],
                'utm_content': ['3193860'],
                'utm_campaign': ['7475040'],
                'irgwc': ['1'],
                'afsrc': ['1']
            }
            query_params.update(impact_params)
            
            new_query = urllib.parse.urlencode(query_params, doseq=True)
            affiliate_url = urllib.parse.urlunparse(
                (parsed.scheme, parsed.netloc, parsed.path, parsed.params, new_query, parsed.fragment)
            )
            return affiliate_url
        except Exception as e:
            return deal.canonical_url
