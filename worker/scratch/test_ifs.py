import time
from playwright.sync_api import sync_playwright
from playwright_stealth import Stealth

def test_indiafreestuff():
    url = "https://www.indiafreestuff.in/active-white-liquid-detergent-10l-mega-jar--lavender-fragrance--powerful-stain-remover--suitable-for-front-load--top-load-washing-machines--bucket-wash--gentle-on-clothes"
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=False)
        page = browser.new_page()
        Stealth().use_sync(page)
        
        page.goto(url, wait_until="domcontentloaded", timeout=60000)
        time.sleep(5) # wait for cloudflare to pass if any
        
        print("Links found:")
        for a in page.locator("a").all():
            try:
                text = a.inner_text().strip().replace('\n', ' ')
                href = a.get_attribute("href") or ""
                if href and (not href.startswith('javascript')) and (not href.startswith('#')):
                    if "amazon" in href.lower() or "amzn" in href.lower() or "shop" in text.lower() or "buy" in text.lower():
                        print(f"TEXT: [{text}] HREF: [{href}]")
            except:
                pass
                
        browser.close()

if __name__ == "__main__":
    test_indiafreestuff()
