import os
import time
from playwright.sync_api import sync_playwright

user_data_dir = os.path.join(os.path.dirname(os.path.dirname(__file__)), 'browser_profile')

with sync_playwright() as p:
    context = p.chromium.launch_persistent_context(
        user_data_dir=user_data_dir,
        headless=True,
        executable_path=r"C:\Program Files\Google\Chrome\Application\chrome.exe",
        args=["--disable-blink-features=AutomationControlled"]
    )
    page = context.pages[0] if context.pages else context.new_page()
    try:
        url = 'https://www.amazon.in/dp/B0D5D4ZTRG' # Example monitor or let's search
        page.goto('https://www.amazon.in/s?k=LS43FM700UWXXL', timeout=30000)
        time.sleep(2)
        links = page.locator('a[href*="/dp/"]').all()
        target_url = None
        for l in links:
            href = l.get_attribute('href')
            if href and ('LS43' in href or '/dp/' in href):
                target_url = 'https://www.amazon.in' + href if href.startswith('/') else href
                break
        print('Target URL:', target_url)
        if target_url:
            page.goto(target_url, timeout=30000)
            time.sleep(3)
            rufus = page.locator('[id*="rufus-price"], [data-query-text="Price history"], .rufus-ingress-div-block').all()
            print('Rufus elements found:', len(rufus))
            for i, r in enumerate(rufus):
                html = r.evaluate('el => el.outerHTML.substring(0, 300)')
                print(f'Element {i}:', html)

            print('Clicking Rufus Price history button...')
            btn = page.locator('#rufus-price-ingress-cc input, #rufus-price-ingress-cc, #rufus-price-ingress-bb input').first
            btn.click()
            print('Clicked! Waiting for Rufus drawer...')
            time.sleep(5)

            # Capture screenshot to see Rufus drawer
            screenshot_path = os.path.join(os.path.dirname(__file__), 'rufus_opened.png')
            page.screenshot(path=screenshot_path)
            print('Saved screenshot to:', screenshot_path)

            # Search all text inside Rufus container
            rufus_drawer = page.locator('[id*="rufus"], [class*="rufus"], [aria-label*="Rufus"]').all()
            print('Rufus drawer elements found:', len(rufus_drawer))
            for rd in rufus_drawer[:5]:
                try:
                    txt = rd.inner_text().strip()
                    if txt:
                        print('Drawer text snippet:', repr(txt[:200]))
                except:
                    pass
    except Exception as e:
        print('Error:', e)
    finally:
        context.close()
