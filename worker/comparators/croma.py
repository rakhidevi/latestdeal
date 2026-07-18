from .base_scraper import BaseScraper
import urllib.parse

class CromaScraper(BaseScraper):
    @property
    def store_name(self) -> str:
        return "Croma"

    async def search(self, title: str) -> dict:
        try:
            search_term = " ".join(title.split()[:5])
            url = f"https://www.croma.com/searchB?q={urllib.parse.quote(search_term)}"
            
            await self.page.goto(url, timeout=30000, wait_until='domcontentloaded')
            
            result = await self.page.evaluate('''() => {
                const item = document.querySelector('.product-item');
                if (!item) return null;
                
                const titleEl = item.querySelector('.product-title a, h3 a');
                const priceEl = item.querySelector('.amount, [data-testid="price"]');
                
                return {
                    url: titleEl ? "https://www.croma.com" + titleEl.getAttribute('href') : null,
                    price: priceEl ? priceEl.innerText.replace(/[^0-9.]/g, '') : null,
                    rating: "4.0", // Dummy rating
                    delivery: "Standard Delivery"
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
            print(f"Croma Scraper Error: {e}")
            return None
