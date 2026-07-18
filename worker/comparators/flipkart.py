from .base_scraper import BaseScraper
import urllib.parse

class FlipkartScraper(BaseScraper):
    @property
    def store_name(self) -> str:
        return "Flipkart"

    async def search(self, title: str) -> dict:
        try:
            search_term = " ".join(title.split()[:5])
            url = f"https://www.flipkart.com/search?q={urllib.parse.quote(search_term)}"
            
            await self.page.goto(url, timeout=30000, wait_until='domcontentloaded')
            
            # Simple selector evaluation for Flipkart
            result = await self.page.evaluate('''() => {
                // Flipkart uses various classes, this is a simplified approach
                const items = document.querySelectorAll('div[data-id]');
                if (!items || items.length === 0) return null;
                
                const item = items[0];
                const linkEl = item.querySelector('a');
                
                // Usually prices have a specific class containing rupee symbol
                const priceMatch = item.innerText.match(/₹([0-9,]+)/);
                
                return {
                    url: linkEl ? "https://www.flipkart.com" + linkEl.getAttribute('href') : null,
                    price: priceMatch ? priceMatch[1].replace(/,/g, '') : null,
                    rating: "4.5 Stars", // Dummy rating for Flipkart
                    delivery: "2 Days"
                };
            }''')
            
            if result and result['price']:
                return {
                    "store": self.store_name,
                    "price": float(result['price']),
                    "url": result['url'] or url,
                    "delivery": result['delivery'],
                    "rating": result['rating']
                }
            return None
        except Exception as e:
            print(f"Flipkart Scraper Error: {e}")
            return None
