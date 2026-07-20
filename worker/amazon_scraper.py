import os
import time
import random
from typing import Optional
from playwright.sync_api import sync_playwright
from playwright_stealth import Stealth

from interfaces import MerchantScraper
from models import Deal, DealCategory, PlaywrightTimeout, AffiliateLinkFailed, ScraperException
from utils import extract_amazon_asin

class AmazonScraper(MerchantScraper):
    
    @classmethod
    def can_handle(cls, domain: str) -> bool:
        return "amazon" in domain or "amzn" in domain

    def extract(self, url: str) -> Deal:
        from browser_utils import setup_browser_stateless, get_page
        with sync_playwright() as p:
            browser, context = setup_browser_stateless(p)
            try:
                page = get_page(context)
                Stealth().use_sync(page)
                
                try:
                    page.goto(url, wait_until="domcontentloaded", timeout=30000)
                    time.sleep(2) # Allow React/hydration to paint prices
                    
                    title_element = page.locator("#productTitle").first
                    title = title_element.inner_text().strip() if title_element.count() > 0 else page.title()
                    
                    if "Robot Check" in title or "CAPTCHA" in title or "Bot Check" in title:
                        raise ScraperException("needs_desktop_processing")
                        
                    if "Page Not Found" in title:
                        raise ScraperException("Deal rejected: Page Not Found (nodeal)")
                    
                    discounted_price_html = ""
                    for selector in [
                        "#corePriceDisplay_desktop_feature_div .a-price-whole",
                        ".priceToPay .a-price-whole",
                        "#priceblock_dealprice",
                        "#priceblock_ourprice"
                    ]:
                        el = page.locator(selector).first
                        if el.count() > 0:
                            discounted_price_html = el.inner_text().strip()
                            break
                            
                    original_price_html = ""
                    for selector in [
                        ".a-text-price .a-offscreen",
                        "#priceBlockStrikePriceString",
                        "span.a-price.a-text-price span.a-offscreen"
                    ]:
                        el = page.locator(selector).first
                        if el.count() > 0:
                            text_val = el.text_content().strip()
                            if "per g" not in text_val.lower() and "/100 g" not in text_val.lower():
                                original_price_html = text_val
                                break
                                
                    image_url = ""
                    for selector in ["#landingImage", "#imgBlkFront", ".a-dynamic-image", "#main-image"]:
                        img_el = page.locator(selector).first
                        if img_el.count() > 0:
                            image_url = img_el.get_attribute("data-old-hires") or img_el.get_attribute("src") or ""
                            if image_url:
                                break
                        
                    star_rating = ""
                    rating_el = page.locator("i[data-hook='average-star-rating'] .a-icon-alt").first
                    if rating_el.count() > 0:
                        star_rating = rating_el.text_content().strip()
                        
                    # Clean prices to floats
                    def clean_price(p_str):
                        if not p_str: return None
                        c = ''.join(c for c in p_str if c.isdigit() or c == '.')
                        try: return float(c) if c else None
                        except: return None
                        
                    curr_price = clean_price(discounted_price_html)
                    orig_price = clean_price(original_price_html)
                    discount = 0.0
                    if curr_price and orig_price and orig_price > curr_price:
                        discount = round(((orig_price - curr_price) / orig_price) * 100, 2)
                        
                    return Deal(
                        merchant="amazon",
                        title=title,
                        price=curr_price,
                        original_price=orig_price,
                        discount_percent=discount,
                        image_url=image_url,
                        canonical_url=url,
                        rating=clean_price(star_rating) if star_rating else None
                    )
                except Exception as e:
                    raise PlaywrightTimeout(f"Amazon extraction failed: {str(e)}")
            finally:
                context.close()
                browser.close()

    def generate_affiliate(self, deal: Deal) -> str:
        """
        Uses persistent profile to get SiteStripe link.
        """
        from browser_utils import setup_browser_persistent, get_page
        with sync_playwright() as p:
            context = setup_browser_persistent(p)
            try:
                page = get_page(context)
                Stealth().use_sync(page)
                
                try:
                    page.goto(deal.canonical_url, wait_until="domcontentloaded", timeout=30000)
                    time.sleep(3)
                    
                    if page.locator("div#amzn-ss-wrap").count() == 0:
                        raise AffiliateLinkFailed("SiteStripe not detected. Please login manually.")
                        
                    page.locator("#amzn-ss-text-link").first.click()
                    page.wait_for_selector("#amzn-ss-copy-affiliate-link-btn-announce", timeout=10000)
                    page.locator("#amzn-ss-copy-affiliate-link-btn-announce").first.click()
                    time.sleep(1) # Let clipboard write
                    
                    short_url = page.evaluate("navigator.clipboard.readText()")
                    from domains import is_amazon_url
                    if not short_url or not is_amazon_url(short_url):
                        raise AffiliateLinkFailed(f"Invalid SiteStripe shortlink: {short_url}")
                        
                    return short_url
                except Exception as e:
                    raise AffiliateLinkFailed(f"Failed to generate SiteStripe Link: {e}")
            finally:
                context.close()
