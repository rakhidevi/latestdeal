import os
import time
import random
from typing import Optional
from playwright.sync_api import sync_playwright
from playwright_stealth import Stealth

from interfaces import MerchantScraper
from models import Deal, DealCategory, PlaywrightTimeout, AffiliateLinkFailed, ScraperException
from utils import extract_amazon_asin

import json
import re

def extract_amazon_brand(page, title: str = "") -> Optional[str]:
    """
    Extracts brand following a strict confidence hierarchy:
    1. JSON-LD Schema.org brand metadata
    2. #bylineInfo / Store link ("Visit the Bajaj Store" -> "Bajaj")
    3. Product Overview Table (.po-brand)
    4. First token of title heuristic
    5. Cleanliness Guard: NEVER allow marketplace name (Amazon, Flipkart, etc.)
    """
    brand = None
    
    # 1. JSON-LD Schema.org
    try:
        scripts = page.locator("script[type='application/ld+json']").all()
        for script in scripts:
            try:
                raw_json = script.text_content()
                if not raw_json:
                    continue
                data = json.loads(raw_json)
                if isinstance(data, dict):
                    if "brand" in data:
                        b = data["brand"]
                        brand = b.get("name") if isinstance(b, dict) else str(b)
                        if brand:
                            break
                    elif "@graph" in data and isinstance(data["@graph"], list):
                        for item in data["@graph"]:
                            if isinstance(item, dict) and "brand" in item:
                                b = item["brand"]
                                brand = b.get("name") if isinstance(b, dict) else str(b)
                                if brand:
                                    break
            except Exception:
                continue
    except Exception:
        pass

    # 2. #bylineInfo / Store link
    if not brand:
        try:
            byline = page.locator("#bylineInfo").first
            if byline.count() > 0:
                raw_text = byline.text_content().strip()
                cleaned = re.sub(r'^(visit\s+the\s+|brand:\s*)', '', raw_text, flags=re.IGNORECASE)
                cleaned = re.sub(r'\s+store$', '', cleaned, flags=re.IGNORECASE).strip()
                if cleaned:
                    brand = cleaned
        except Exception:
            pass

    # 3. Product Overview Table (.po-brand / Brand row)
    if not brand:
        try:
            for selector in [
                "tr.po-brand td.po-break-word",
                "tr.po-brand span.po-break-word",
                "tr:has-text('Brand') td.po-break-word",
                "tr:has-text('Brand') td:nth-child(2)",
                "div#productOverview_feature_div tr:has-text('Brand') td:last-child"
            ]:
                el = page.locator(selector).first
                if el.count() > 0:
                    text_val = el.text_content().strip()
                    if text_val:
                        brand = text_val
                        break
        except Exception:
            pass

    # 4. Fallback Title Extraction
    if not brand and title:
        parts = title.split()
        if parts:
            first_word = parts[0].strip(",.:;|/- ")
            if len(first_word) >= 2 and not first_word.isdigit() and first_word.lower() not in ["new", "best", "the", "all", "pack", "set", "buy"]:
                brand = first_word

    # 5. Cleanliness Guard: NEVER allow marketplace / merchant name to be the brand
    if brand:
        brand = brand.strip()
        if brand.lower() in ["amazon", "amazon.in", "flipkart", "myntra", "direct", "unknown"]:
            return None
        if "amazonbasics" in brand.lower() or "amazon basics" in brand.lower():
            return "Amazon Basics"

    return brand


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
                        
                    # Extract Brand using Confidence Hierarchy
                    resolved_brand = extract_amazon_brand(page, title)
                    
                    return Deal(
                        merchant="amazon",
                        title=title,
                        brand=resolved_brand,
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
