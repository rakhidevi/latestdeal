from .base_scraper import BaseScraper
import urllib.parse
import re

class AmazonScraper(BaseScraper):
    @property
    def store_name(self) -> str:
        return "Amazon"

    async def search(self, title: str) -> dict:
        try:
            # Shorten title for better search results
            search_term = " ".join(title.split()[:5])
            url = f"https://www.amazon.in/s?k={urllib.parse.quote(search_term)}"
            
            await self.page.goto(url, timeout=30000, wait_until='domcontentloaded')
            
            # Extract first result
            result = await self.page.evaluate('''() => {
                const item = document.querySelector('[data-component-type="s-search-result"]');
                if (!item) return null;
                
                const titleEl = item.querySelector('h2 a');
                const priceEl = item.querySelector('.a-price-whole');
                const ratingEl = item.querySelector('.a-icon-alt');
                
                return {
                    url: titleEl ? "https://www.amazon.in" + titleEl.getAttribute('href') : null,
                    price: priceEl ? priceEl.innerText.replace(/,/g, '') : null,
                    rating: ratingEl ? ratingEl.innerText : null,
                    delivery: "Prime Delivery" // Hardcoded for simplicity here
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
            print(f"Amazon Scraper Error: {e}")
            return None
