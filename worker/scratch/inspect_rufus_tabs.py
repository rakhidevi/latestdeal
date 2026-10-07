import os, sys, time, re
from playwright.sync_api import sync_playwright

sys.stdout.reconfigure(encoding='utf-8')

user_data_dir = os.path.join(os.path.dirname(os.path.dirname(__file__)), 'browser_profile')
with sync_playwright() as p:
    ctx = p.chromium.launch_persistent_context(
        user_data_dir=user_data_dir,
        headless=False,
        executable_path=r"C:\Program Files\Google\Chrome\Application\chrome.exe",
        args=["--disable-blink-features=AutomationControlled"]
    )
    page = ctx.pages[0] if ctx.pages else ctx.new_page()
    url = "https://www.amazon.in/dp/B0FJYJ8LVY"
    page.goto(url, wait_until="domcontentloaded", timeout=30000)
    time.sleep(3)
    
    # Click Rufus price history
    btn = page.locator("#rufus-price-ingress-cc input, #rufus-price-ingress-cc, [data-query-text='Price history']").first
    btn.click(force=True)
    time.sleep(6)
    
    def extract_chart_data(tab_index):
        # Click tab: 0=1M, 1=3M, 2=1Y
        tab = page.locator(f'button[data-pricing-window-index="{tab_index}"]').first
        if tab.count() > 0:
            tab.click(force=True)
            time.sleep(1.5)
            
        txt = page.locator("body").inner_text()
        idx = txt.find("Prices reflect the lowest")
        if idx != -1:
            snippet = txt[max(0, idx-250):idx]
            prices = [float(p.replace(',', '')) for p in re.findall(r'₹\s*([\d,]+)', snippet) if len(p.replace(',', '')) >= 4]
            return prices
        return []

    p_1m = extract_chart_data(0)
    p_3m = extract_chart_data(1)
    p_1y = extract_chart_data(2)

    print("1M Chart Prices:", p_1m)
    print("3M Chart Prices:", p_3m)
    print("1Y Chart Prices:", p_1y)

    ctx.close()
