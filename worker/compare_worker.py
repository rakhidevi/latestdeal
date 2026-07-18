import asyncio
import os
import requests
import time
from dotenv import load_dotenv

from browser_pool import BrowserPool
from comparators.amazon import AmazonScraper
from comparators.flipkart import FlipkartScraper
from comparators.croma import CromaScraper
from ai_scorer import calculate_value_score

load_dotenv()

API_BASE = os.getenv('API_URL', 'http://latestdeal.test/api/v1')

async def process_job(job, pool: BrowserPool):
    job_id = job['id']
    title = job['title']
    deal_price = job.get('deal_price')
    print(f"Processing Job {job_id} for title: {title} (Price: {deal_price})")
    
    # Use a single tab for all scraping to prevent memory leaks (AGENTS.md Rule 2)
    page = await pool.get_page()
    
    amazon = AmazonScraper(page)
    flipkart = FlipkartScraper(page)
    croma = CromaScraper(page)
    
    # Run scrapers sequentially to reuse the same tab
    results = []
    
    try:
        results.append(await amazon.search(title))
    except Exception as e:
        results.append(e)
        
    try:
        results.append(await flipkart.search(title))
    except Exception as e:
        results.append(e)
        
    try:
        results.append(await croma.search(title))
    except Exception as e:
        results.append(e)
    
    valid_results = []
    for r in results:
        if isinstance(r, dict):
            valid_results.append(r)
        elif isinstance(r, Exception):
            print(f"Scraper exception: {r}")
            
    score = await calculate_value_score(title, deal_price, valid_results)
    
    # Push back to Laravel
    try:
        resp = requests.post(
            f"{API_BASE}/worker/compare-jobs/{job_id}/complete",
            json={
                "results": valid_results,
                "ai_score": score
            },
            timeout=10
        )
        print(f"Job {job_id} completed. Laravel response: {resp.status_code}")
    except Exception as e:
        print(f"Failed to push job {job_id} results to Laravel: {e}")

async def main():
    print("Starting Comparison Worker daemon...")
    pool = BrowserPool()
    await pool.start()
    
    try:
        while True:
            try:
                resp = requests.get(f"{API_BASE}/worker/compare-jobs/pending", timeout=10)
                if resp.status_code == 200:
                    data = resp.json()
                    job = data.get('job')
                    if job:
                        await process_job(job, pool)
                    else:
                        await asyncio.sleep(2)
                else:
                    await asyncio.sleep(5)
            except requests.exceptions.RequestException as e:
                print(f"Connection error to Laravel backend: {e}")
                await asyncio.sleep(5)
    except KeyboardInterrupt:
        print("Shutting down worker...")
    finally:
        await pool.close()

if __name__ == "__main__":
    asyncio.run(main())
