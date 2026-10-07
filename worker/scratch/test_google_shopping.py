import asyncio, urllib.parse, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')
from playwright.async_api import async_playwright

async def test_google_shopping():
    query = "Sony WH-1000XM4 Wireless Headphones"
    url = f"https://www.google.com/search?q={urllib.parse.quote(query)}&tbm=shop&gl=in&hl=en"
    print(f"Testing Google Shopping search: {url}")
    
    async with async_playwright() as p:
        browser = await p.chromium.launch(
            headless=True,
            executable_path=r"C:\Program Files\Google\Chrome\Application\chrome.exe",
            args=["--disable-blink-features=AutomationControlled"]
        )
        context = await browser.new_context(
            user_agent="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
        )
        page = await context.new_page()
        
        try:
            await page.goto(url, wait_until="domcontentloaded", timeout=15000)
            await asyncio.sleep(2)
            
            title = await page.title()
            print("Page Title:", title)
            
            if "sorry" in page.url or "captcha" in page.url.lower():
                print("🚨 Google CAPTCHA triggered!")
                return
                
            results = await page.evaluate('''() => {
                const items = [];
                // Find Google Shopping product cards
                const cards = document.querySelectorAll('.sh-dgr__grid-result, .sh-np__click-target, div[data-docid]');
                for (const c of cards) {
                    const text = c.innerText;
                    // Look for price pattern
                    const priceMatch = text.match(/₹\s*([0-9,]+)/);
                    if (priceMatch) {
                        // Find merchant/store name
                        const lines = text.split('\\n').map(l => l.trim()).filter(l => l.length > 0);
                        items.push({
                            snippet: lines.slice(0, 4),
                            price: priceMatch[1]
                        });
                    }
                }
                return items.slice(0, 5);
            }''')
            
            print(f"Extracted {len(results)} shopping items:")
            for i, it in enumerate(results):
                print(f" Item {i+1}: Price ₹{it['price']} | Lines: {it['snippet']}")
                
        finally:
            await browser.close()

if __name__ == '__main__':
    asyncio.run(test_google_shopping())
