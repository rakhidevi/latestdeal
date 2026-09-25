import os
import time
import random
import re
from playwright.sync_api import sync_playwright
from playwright_stealth import Stealth
from filelock import FileLock, Timeout
from utils import clean_amazon_url
from domains import AMAZON_PRODUCT_PREFIXES

def extract_rufus_price_history(page) -> dict:
    """
    Detects and clicks the native Amazon Rufus AI 'Price history' ingress widget.
    Extracts 30-day, 90-day (3M), and 365-day (1Y) price ranges.
    """
    history = {}
    try:
        # Scroll to price section to trigger hydration of dynamic Rufus ingress widgets
        try:
            price_box = page.locator("#corePriceDisplay_desktop_feature_div, .priceToPay, #unifiedPrice_feature_div").first
            if price_box.count() > 0:
                price_box.scroll_into_view_if_needed(timeout=2000)
            else:
                page.evaluate("window.scrollBy(0, 350)")
            time.sleep(1.5)
        except Exception:
            pass

        ingress_selectors = [
            "#rufus-price-ingress-cc input",
            "#rufus-price-ingress-cc",
            "#rufus-price-ingress-bb input",
            "#rufus-price-ingress-bb",
            "[data-query-text='Price history']",
            ".rufus-ingress-div-block"
        ]
        
        # Wait up to 3 seconds for Rufus widget to hydrate
        try:
            page.wait_for_selector(", ".join(ingress_selectors), timeout=3000)
        except Exception:
            pass

        btn = None
        for sel in ingress_selectors:
            loc = page.locator(sel).first
            if loc.count() > 0:
                btn = loc
                break

        if not btn:
            print("[Rufus AI] Price history button not present on this product (Amazon displays Rufus on select categories/items).")
            return None

        print("[Rufus AI] Found native 'Price history' button. Clicking...")
        btn.scroll_into_view_if_needed()
        time.sleep(0.5)
        btn.click(force=True)

        # Verify drawer opening, retry click if necessary
        time.sleep(1.5)
        if page.locator('button[data-pricing-window-index], .rufus-pricing-button, [aria-label*="Rufus"]').count() == 0:
            try:
                page.evaluate("el => el.click()", btn.element_handle())
            except Exception:
                pass

        def parse_chart_ticks(full_text):
            """Parses the price ticks displayed on the interactive Rufus graph."""
            idx = full_text.find("Prices reflect the lowest")
            if idx != -1:
                start_idx = full_text.rfind("1Y", 0, idx)
                snippet = full_text[start_idx:idx] if start_idx != -1 else full_text[max(0, idx-300):idx]
                ticks = [float(p.replace(',', '')) for p in re.findall(r'₹\s*([\d,]+)', snippet) if p.replace(',', '').isdigit() and float(p.replace(',', '')) > 0]
                if ticks:
                    return sorted(ticks)
            return []

        # 1. Dynamically wait up to 25 seconds for Rufus AI to generate the price history response
        print("[Rufus AI] Waiting for Rufus AI to generate price history...")
        start_t = time.time()
        
        while time.time() - start_t < 25:
            body_text = page.locator("body").inner_text()
            if "Please sign in to begin using Rufus" in body_text:
                print("[Rufus AI] Rufus requires Amazon sign-in. Please log into Amazon via launch_browser.py.")
                return None
            
            if "ranged from" in body_text or parse_chart_ticks(body_text):
                break
                
            time.sleep(1.0)

        body_text = page.locator("body").inner_text()
        m_30 = re.search(r'ranged from\s*[₹\s]*([\d,]+)\s*to\s*[₹\s]*([\d,]+)', body_text, re.IGNORECASE)
        if m_30:
            history["history_30d_low"] = float(m_30.group(1).replace(',', ''))
            history["history_30d_high"] = float(m_30.group(2).replace(',', ''))
            print(f"[Rufus AI] Extracted 30D Range from text: ₹{history['history_30d_low']} - ₹{history['history_30d_high']}")
        else:
            ticks_1m = parse_chart_ticks(body_text)
            if ticks_1m:
                history["history_30d_low"] = min(ticks_1m)
                history["history_30d_high"] = max(ticks_1m)
                print(f"[Rufus AI] Extracted 30D Range from chart ticks: ₹{history['history_30d_low']} - ₹{history['history_30d_high']}")
            else:
                print("[Rufus AI] Price history did not finish generating within timeout.")
                return None



        # 2. Click 3M (90-Day) Tab
        try:
            try:
                page.wait_for_selector('button[data-pricing-window-index="1"], button:has-text("3M"), [aria-label*="3 month"]', timeout=8000)
            except Exception:
                pass

            tab_3m = page.locator('button[data-pricing-window-index="1"], button:has-text("3M"), [aria-label*="3 month"]').first
            if tab_3m.count() > 0:
                print("[Rufus AI] Clicking 3M (90-Day) tab...")
                tab_3m.scroll_into_view_if_needed()
                tab_3m.click(force=True)
                time.sleep(2.0)
                text_3m = page.locator("body").inner_text()
                
                # Method A: Text summary if present
                ranges = re.findall(r'(?:past\s*3\s*months|past\s*90\s*days|3M).*?ranged from\s*[₹\s]*([\d,]+)\s*to\s*[₹\s]*([\d,]+)', text_3m, re.IGNORECASE)
                if ranges:
                    history["history_90d_low"] = float(ranges[-1][0].replace(',', ''))
                    history["history_90d_high"] = float(ranges[-1][1].replace(',', ''))
                    history["history_90d_median"] = round((history["history_90d_low"] + history["history_90d_high"]) / 2.0, 2)
                    print(f"[Rufus AI] Extracted 90D Range from text: ₹{history['history_90d_low']} - ₹{history['history_90d_high']}")
                else:
                    # Method B: Exact Y-axis ticks of the interactive 3M chart
                    ticks_3m = parse_chart_ticks(text_3m)
                    if ticks_3m:
                        history["history_90d_low"] = min(ticks_3m)
                        history["history_90d_high"] = max(ticks_3m)
                        history["history_90d_median"] = ticks_3m[len(ticks_3m)//2]
                        print(f"[Rufus AI] Extracted 90D Range from chart ticks: ₹{history['history_90d_low']} - ₹{history['history_90d_high']} (Median: ₹{history['history_90d_median']})")
            else:
                print("[Rufus AI] 3M tab not detected in DOM.")
        except Exception as e3:
            print(f"[Rufus AI] 3M tab extraction note: {e3}")

        # 3. Click 1Y (365-Day) Tab
        try:
            try:
                page.wait_for_selector('button[data-pricing-window-index="2"], button:has-text("1Y"), [aria-label*="1 year"]', timeout=5000)
            except Exception:
                pass

            tab_1y = page.locator('button[data-pricing-window-index="2"], button:has-text("1Y"), [aria-label*="1 year"]').first
            if tab_1y.count() > 0:
                print("[Rufus AI] Clicking 1Y (365-Day) tab...")
                tab_1y.scroll_into_view_if_needed()
                tab_1y.click(force=True)
                time.sleep(2.0)
                text_1y = page.locator("body").inner_text()
                
                # Method A: Text summary if present
                ranges_y = re.findall(r'(?:past\s*(?:year|12\s*months|365\s*days)|1Y).*?ranged from\s*[₹\s]*([\d,]+)\s*to\s*[₹\s]*([\d,]+)', text_1y, re.IGNORECASE)
                if ranges_y:
                    history["history_365d_low"] = float(ranges_y[-1][0].replace(',', ''))
                    history["history_365d_high"] = float(ranges_y[-1][1].replace(',', ''))
                    print(f"[Rufus AI] Extracted 365D Range from text: ₹{history['history_365d_low']} - ₹{history['history_365d_high']}")
                else:
                    # Method B: Exact Y-axis ticks of the interactive 1Y chart
                    ticks_1y = parse_chart_ticks(text_1y)
                    if ticks_1y:
                        history["history_365d_low"] = min(ticks_1y)
                        history["history_365d_high"] = max(ticks_1y)
                        print(f"[Rufus AI] Extracted 365D Range from chart ticks: ₹{history['history_365d_low']} - ₹{history['history_365d_high']}")
            else:
                print("[Rufus AI] 1Y tab not detected in DOM.")
        except Exception as ey:
            print(f"[Rufus AI] 1Y tab extraction note: {ey}")

        # 4. Close the drawer cleanly
        time.sleep(1.5)
        try:
            close_btn = page.locator('button[aria-label="Close"], #rufus-close, [aria-label*="close"], button:has-text("✕")').first
            if close_btn.count() > 0:
                close_btn.click(force=True)
            else:
                page.keyboard.press("Escape")
        except:
            pass

        if history:
            history["source"] = "rufus_ai"
            return history

    except Exception as e:
        print(f"[Rufus AI] Extraction error: {e}")

    return None

def get_sitestripe_link_and_data(url: str) -> dict:
    """
    Uses a persistent Playwright browser to generate short links via SiteStripe.
    """
    lock_path = os.path.join(os.path.dirname(__file__), "browser_profile.lock")
    lock = FileLock(lock_path, timeout=120)
    
    try:
        with lock:
            return _execute_sitestripe_scrape(url)
    except Timeout:
        print("[BrowserLock] Timed out waiting for browser profile lock (another worker is currently using Chrome).")
        return None

def _execute_sitestripe_scrape(url: str) -> dict:
    with sync_playwright() as p:
        user_data_dir = os.path.join(os.path.dirname(__file__), 'browser_profile')
        os.makedirs(user_data_dir, exist_ok=True)
        
        context = None
        for attempt in range(2):
            try:
                # Launch Chrome visibly so the user can log in if needed
                context = p.chromium.launch_persistent_context(
                    user_data_dir=user_data_dir,
                    headless=False,
                    executable_path=r"C:\Program Files\Google\Chrome\Application\chrome.exe", # Use REAL local Chrome
                    permissions=["clipboard-read", "clipboard-write"], # Grant clipboard permissions
                    args=["--disable-blink-features=AutomationControlled"]
                )
                
                # Aggressively close any extra tabs that pop in from asynchronous session restore
                def close_extra_page(p):
                    try:
                        p.close()
                    except:
                        pass
                context.on("page", close_extra_page)
                
                # Clean up any restored tabs from previous sessions
                time.sleep(1)
                while len(context.pages) > 1:
                    context.pages[-1].close()
                try:
                    context.remove_listener("page", close_extra_page)
                except Exception:
                    pass
                page = context.pages[0] if context.pages else context.new_page()
                Stealth().use_sync(page)
                break
            except Exception as e:
                print(f"ERROR: Playwright browser profile is LOCKED! {e}")
                if attempt == 0:
                    import subprocess, sys
                    print("Attempting to auto-kill zombie Chrome processes and retry...")
                    subprocess.run([sys.executable, os.path.join(os.path.dirname(__file__), "kill_zombie_chrome.py")])
                    time.sleep(2)
                else:
                    print("Please close any extra Chrome windows or restart your computer to clear the locks.")
                    return False
            
        try:
            print(f"Navigating to raw URL: {url}...")
            # Use domcontentloaded so heavy tracking beacons don't freeze navigation for 60 seconds
            try:
                page.goto(url, wait_until="domcontentloaded", timeout=30000)
            except Exception as eg:
                print(f"Navigation warning: {eg}")
            
            # Wait for dynamic DOM elements to settle
            time.sleep(2)
            
            final_raw_url = page.url
            print(f"Resolved raw URL: {final_raw_url}")
            
            # If we are on an aggregator like IndiaFreeStuff, try to find the actual deal link
            if "indiafreestuff.in" in final_raw_url.lower():
                print("Detected IndiaFreeStuff page. Looking for merchant link...")
                try:
                    shop_now_link = None
                    
                    # First, try to find a button with exactly "Shop now" text
                    for a in page.locator("a").all():
                        text = a.inner_text().strip().lower()
                        href = a.get_attribute("href") or ""
                        if "shop now" in text:
                            shop_now_link = href
                            break
                    
                    # Fallback if no "Shop now" text is found but there's a link to amazon
                    if not shop_now_link:
                        for a in page.locator("a").all():
                            href = a.get_attribute("href") or ""
                            href_lower = href.lower()
                            if any(prefix in href_lower for prefix in AMAZON_PRODUCT_PREFIXES):
                                shop_now_link = href
                                break
                                
                    if shop_now_link:
                        print(f"Found merchant link: {shop_now_link}. Navigating...")
                        page.goto(shop_now_link, wait_until="domcontentloaded", timeout=60000)
                        time.sleep(3)
                        final_raw_url = page.url
                        print(f"Resolved merchant URL: {final_raw_url}")
                    else:
                        print("Could not find a 'Shop now' or Amazon link on IndiaFreeStuff page.")
                except Exception as e:
                    print(f"Failed to extract merchant link from IndiaFreeStuff: {e}")
                    
            # Clean the URL
            clean_url = clean_amazon_url(final_raw_url, resolve_redirects=False)
            
            if clean_url != final_raw_url and "amazon" in clean_url.lower():
                print(f"URL cleaned successfully. Revisiting clean URL: {clean_url}...")
                page.goto(clean_url, wait_until="domcontentloaded", timeout=60000)
            else:
                clean_url = final_raw_url
            
            has_sitestripe = True
            if page.locator("div#amzn-ss-wrap").count() == 0:
                # Give it a second just in case it's loading slowly
                time.sleep(2)
                if page.locator("div#amzn-ss-wrap").count() == 0:
                    print("[SiteStripe] SiteStripe bar not detected on this page/session. Continuing with Rufus AI & product extraction.")
                    has_sitestripe = False
                else:
                    print("SiteStripe bar detected.")
            else:
                print("SiteStripe bar detected.")
            
            print("Page loaded. Waiting for human delay...")
            time.sleep(random.uniform(5.0, 10.0))
            
            # 1. Extract DOM Data (Title, Prices, Image)
            print("Extracting DOM product data...")
            
            title_element = page.locator("#productTitle").first
            title = title_element.inner_text().strip() if title_element.count() > 0 else page.title()
            
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
                # There can be multiple matches. The first might be the unit price.
                # Check all matches for the selector
                elements = page.locator(selector).all()
                for el in elements:
                    text_val = el.text_content().strip()
                    # Check parent for unit price text
                    parent = el.locator("..")
                    parent_text = parent.text_content().lower() if parent.count() > 0 else ""
                    # Also check grandparent to be safe
                    grandparent = parent.locator("..") if parent.count() > 0 else None
                    grandparent_text = grandparent.text_content().lower() if grandparent and grandparent.count() > 0 else ""
                    
                    full_context = parent_text + " " + grandparent_text
                    
                    if "per g" not in full_context and "/100" not in full_context and "per 100" not in full_context and "/ 100" not in full_context and "/ count" not in full_context and "/count" not in full_context:
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
            img_element = page.locator("#landingImage").first
            if img_element.count() > 0:
                image_url = img_element.get_attribute("data-old-hires") or img_element.get_attribute("src") or ""
                
            features = []
            for el in page.locator("#feature-bullets ul li span.a-list-item").all():
                text = el.inner_text().strip()
                if text: features.append(text)
                
            star_rating = ""
            for selector in [
                "#averageCustomerReviews .a-icon-alt",
                "#acrPopover",
                "i[data-hook='average-star-rating'] .a-icon-alt"
            ]:
                rating_el = page.locator(selector).first
                if rating_el.count() > 0:
                    # Some elements have the text hidden, or use title attribute
                    val = rating_el.get_attribute("title") or rating_el.text_content()
                    if val and "out of 5" in val.lower():
                        star_rating = val.strip()
                        break
            
            review_count = ""
            review_el = page.locator("#acrCustomerReviewText").first
            if review_el.count() > 0:
                raw_text = review_el.text_content().strip()
                import re
                cleaned = re.sub(r'[^\d]', '', raw_text)
                if cleaned:
                    review_count = int(cleaned)
                
            # 1.5 Extract Prime / FBA status
            is_prime = False
            is_fulfilled = False
            try:
                if page.locator("i.a-icon-prime").count() > 0:
                    is_prime = True
                elif page.locator("#primeSavingsBadge").count() > 0:
                    badge_txt = page.locator("#primeSavingsBadge").first.inner_text().lower()
                    if "prime" in badge_txt:
                        is_prime = True
            except Exception:
                pass

            try:
                if page.locator("span.a-declarative:has-text('Fulfilled by Amazon')").count() > 0:
                    is_fulfilled = True
                elif page.locator("#merchant-info").count() > 0:
                    merchant_txt = page.locator("#merchant-info").first.inner_text().lower()
                    if "fulfilled by amazon" in merchant_txt:
                        is_fulfilled = True
            except Exception:
                pass

            # 2. SiteStripe Automation
            short_url = ""
            if has_sitestripe:
                print("Looking for SiteStripe bar...")
                try:
                    page.wait_for_selector("#amzn-ss-text-link", timeout=10000)
                    sitestripe_text_btn = page.locator("#amzn-ss-text-link").first
                        
                    print("Clicking SiteStripe 'Get Link' button...")
                    sitestripe_text_btn.click(force=True)
                    
                    import pyperclip
                    pyperclip.copy("") # Clear clipboard first
                    
                    # Wait for popover to appear
                    print("Waiting for popover...")
                    page.wait_for_selector("#amzn-ss-copy-affiliate-link-btn-announce", timeout=10000)
                    
                    copy_btn = page.locator("#amzn-ss-copy-affiliate-link-btn-announce").first
                    copy_btn.click(force=True)
                    
                    # Wait for the "Copied to clipboard" toast to ensure it copied
                    try:
                        page.wait_for_selector("#amzn-ss-copy-toast:not([style*='display: none'])", timeout=5000)
                    except Exception as e:
                        print(f"Toast didn't appear, trying clipboard anyway: {e}")
                        
                    time.sleep(1) # Extra buffer for clipboard to write
                    
                    try:
                        short_url = page.evaluate("navigator.clipboard.readText()")
                    except:
                        short_url = ""
                    
                    if not short_url or ("amzn.to" not in short_url and "link.amazon" not in short_url):
                        print("Browser clipboard API failed. Falling back to OS clipboard (pyperclip)...")
                        short_url = pyperclip.paste()

                    if not short_url or ("amzn.to" not in short_url and "link.amazon" not in short_url):
                        print(f"Failed to extract valid short URL from clipboard. Found: {short_url}")
                        short_url = ""
                    else:
                        print(f"Successfully generated SiteStripe Link: {short_url}")
                        try:
                            pop_close = page.locator(".a-popover-header button.a-button-close, [aria-label*='Close'], button.a-button-close").first
                            if pop_close.count() > 0:
                                pop_close.click(force=True)
                            page.keyboard.press("Escape")
                            time.sleep(1.0)
                        except:
                            pass
                except Exception as e:
                    # Check for "Frequently Returned Item" which disables the Get Link button
                    page_text = page.content()
                    if "Frequently Returned Item" in page_text or "lower return rates" in page_text:
                        print("Deal REJECTED: Frequently Returned Item (SiteStripe 'Get Link' disabled)")
                        return False
                    print(f"SiteStripe bar not found or failed to copy! Error: {e}")
                    print("Returning raw data without shortlink.")

            # 2.5 Extract Rufus AI Price History
            print("Checking for Rufus AI Price History...")
            rufus_history = extract_rufus_price_history(page)
            if rufus_history:
                print(f"[Rufus AI] Attached price history to deal: {rufus_history}")
            else:
                print("[Rufus AI] No Price history available or sign-in required.")
            
            raw_data = {
                "url": clean_url,
                "sitestripe_url": short_url,
                "raw_title": title,
                "raw_discounted_price": discounted_price_html,
                "raw_original_price": original_price_html,
                "features": features,
                "image_url": image_url,
                "star_rating": star_rating,
                "review_count": review_count,
                "is_prime": is_prime,
                "is_fulfilled": is_fulfilled,
                "price_history": rufus_history,
                "scraper_type": "SiteStripe Automation"
            }
            
            return raw_data
        
        finally:
            if context:
                context.close()
        

if __name__ == "__main__":
    import sys
    if len(sys.argv) < 2:
        print("Usage: python sitestripe_scraper.py <amazon-url>")
        sys.exit(1)
    
    data = get_sitestripe_link_and_data(sys.argv[1])
    import json
    print(json.dumps(data, indent=2))
