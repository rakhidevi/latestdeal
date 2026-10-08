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
        from browser_utils import setup_browser_persistent, setup_browser_stateless, get_page
        from sitestripe_scraper import extract_sitestripe_link, extract_rufus_price_history
        from utils import clean_amazon_url
        from filelock import FileLock, Timeout

        cleaned_url = clean_amazon_url(url, resolve_redirects=False)

        with sync_playwright() as p:
            context = None
            browser = None
            is_persistent = False
            lock = None

            # Try persistent context first to extract SiteStripe + Rufus in a single pass
            lock_dir = os.path.join(os.path.dirname(__file__), "browser_profiles")
            os.makedirs(lock_dir, exist_ok=True)
            lock_path = os.path.join(lock_dir, "sitestripe.lock")
            lock = FileLock(lock_path, timeout=5)

            try:
                lock.acquire()
                context = setup_browser_persistent(p, profile_name="sitestripe")
                is_persistent = True
            except (Timeout, Exception) as pe:
                print(f"[AmazonScraper] Persistent profile unavailable ({pe}). Falling back to stateless browser.")
                if lock and lock.is_locked:
                    try:
                        lock.release()
                    except Exception:
                        pass
                browser, context = setup_browser_stateless(p)
                is_persistent = False

            try:
                page = get_page(context)
                Stealth().use_sync(page)

                try:
                    page.goto(cleaned_url, wait_until="domcontentloaded", timeout=35000)
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
                        elements = page.locator(selector).all()
                        for el in elements:
                            text_val = el.text_content().strip()
                            parent = el.locator("..")
                            parent_text = parent.text_content().lower() if parent.count() > 0 else ""
                            if "per g" not in parent_text and "/100" not in parent_text and "per 100" not in parent_text and "/ count" not in parent_text and "/count" not in parent_text:
                                original_price_html = text_val
                                break
                        if original_price_html:
                            break

                    if not original_price_html or "per" in original_price_html.lower() or "/100" in original_price_html.lower():
                        mrp_label = page.locator("span:has-text('M.R.P.:')").first
                        if mrp_label.count() > 0:
                            parent = mrp_label.locator("..").first
                            if parent.count() > 0:
                                raw_txt = parent.text_content()
                                raw_txt = re.sub(r'\([^)]*?(?:per|\/|100\s*g|100\s*ml|kg|count)[^)]*?\)', '', raw_txt, flags=re.IGNORECASE)
                                raw_txt = re.sub(r'₹?\s*[\d,.]+\s*(?:\/|\bper\b)\s*\d*\s*(?:g|kg|ml|l|count|unit|100\s*g|100\s*ml)\b', '', raw_txt, flags=re.IGNORECASE)
                                original_price_html = raw_txt.replace('M.R.P.:', '').strip()

                    image_url = ""
                    for selector in ["#landingImage", "#imgBlkFront", ".a-dynamic-image", "#main-image"]:
                        img_el = page.locator(selector).first
                        if img_el.count() > 0:
                            image_url = img_el.get_attribute("data-old-hires") or img_el.get_attribute("src") or ""
                            if image_url:
                                break

                    star_rating = ""
                    for selector in [
                        "#averageCustomerReviews .a-icon-alt",
                        "#acrPopover",
                        "i[data-hook='average-star-rating'] .a-icon-alt"
                    ]:
                        rating_el = page.locator(selector).first
                        if rating_el.count() > 0:
                            val = rating_el.get_attribute("title") or rating_el.text_content()
                            if val and "out of 5" in val.lower():
                                star_rating = val.strip()
                                break

                    features = []
                    for el in page.locator("#feature-bullets ul li span.a-list-item").all():
                        txt = el.inner_text().strip()
                        if txt: features.append(txt)

                    # Clean prices to floats
                    def clean_price(p_str):
                        if not p_str: return None
                        c = ''.join(c for c in str(p_str) if c.isdigit() or c == '.')
                        try: return float(c) if c else None
                        except: return None

                    curr_price = clean_price(discounted_price_html)
                    orig_price = clean_price(original_price_html)
                    discount = 0.0
                    if curr_price and orig_price and orig_price > curr_price:
                        discount = round(((orig_price - curr_price) / orig_price) * 100, 2)

                    # Extract Brand using Confidence Hierarchy
                    resolved_brand = extract_amazon_brand(page, title)

                    # Single-Pass SiteStripe & Rufus AI Extraction if persistent/authenticated
                    sitestripe_url = ""
                    rufus_history = None

                    if is_persistent:
                        try:
                            sitestripe_url = extract_sitestripe_link(page)
                        except Exception as sse:
                            print(f"[AmazonScraper] SiteStripe extraction notice: {sse}")

                        try:
                            rufus_history = extract_rufus_price_history(page)
                        except Exception as rhe:
                            print(f"[AmazonScraper] Rufus AI extraction notice: {rhe}")

                    deal = Deal(
                        merchant="amazon",
                        title=title,
                        brand=resolved_brand,
                        price=curr_price,
                        original_price=orig_price,
                        discount_percent=discount,
                        image_url=image_url,
                        canonical_url=cleaned_url,
                        affiliate_url=sitestripe_url if sitestripe_url else cleaned_url,
                        rating=clean_price(star_rating) if star_rating else None,
                        features=features
                    )

                    if features:
                        deal.features = features
                    if rufus_history:
                        deal.price_history_raw = rufus_history

                    return deal
                except ScraperException:
                    raise
                except Exception as e:
                    raise PlaywrightTimeout(f"Amazon extraction failed: {str(e)}")
            finally:
                if context:
                    try:
                        context.close()
                    except Exception:
                        pass
                if browser:
                    try:
                        browser.close()
                    except Exception:
                        pass
                if is_persistent and lock and lock.is_locked:
                    try:
                        lock.release()
                    except Exception:
                        pass

    def generate_affiliate(self, deal: Deal) -> str:
        """
        Uses persistent profile to get SiteStripe link if not already extracted.
        """
        if deal.affiliate_url and ("amzn.to" in deal.affiliate_url or "link.amazon" in deal.affiliate_url):
            return deal.affiliate_url

        from browser_utils import setup_browser_persistent, get_page
        from sitestripe_scraper import extract_sitestripe_link
        from filelock import FileLock, Timeout

        lock_dir = os.path.join(os.path.dirname(__file__), "browser_profiles")
        os.makedirs(lock_dir, exist_ok=True)
        lock_path = os.path.join(lock_dir, "sitestripe.lock")
        lock = FileLock(lock_path, timeout=30)

        with lock:
            with sync_playwright() as p:
                context = setup_browser_persistent(p, profile_name="sitestripe")
                try:
                    page = get_page(context)
                    Stealth().use_sync(page)
                    page.goto(deal.canonical_url, wait_until="domcontentloaded", timeout=30000)
                    short_url = extract_sitestripe_link(page)
                    if not short_url:
                        raise AffiliateLinkFailed("Failed to extract SiteStripe Link")
                    return short_url
                finally:
                    context.close()
