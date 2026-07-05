import asyncio
import os
os.environ['PYTHONUTF8']='1'
from crawl4ai import AsyncWebCrawler

async def run():
    async with AsyncWebCrawler(verbose=False) as crawler:
        res = await crawler.arun('https://www.amazon.in/Native-Purifier-RO-Copper-Alkaline/dp/B0D79G62J3')
        
        print("HTML length:", len(res.html))
        
        from bs4 import BeautifulSoup
        soup = BeautifulSoup(res.html, 'html.parser')
        
        title = soup.find(id='productTitle')
        print("Title:", title.text.strip() if title else 'Not found')
        
        price = soup.find(class_='a-price-whole')
        print("Price:", price.text.strip() if price else 'Not found')

if __name__ == "__main__":
    asyncio.run(run())
