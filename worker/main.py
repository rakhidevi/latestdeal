import sys
import io
import os
sys.path.append(os.path.abspath(os.path.join(os.path.dirname(__file__), '..')))
if sys.stdout.encoding.lower() != 'utf-8':
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace', line_buffering=True)
if sys.stderr.encoding.lower() != 'utf-8':
    sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding='utf-8', errors='replace', line_buffering=True)

import time
import os
import asyncio
import json
import websockets
from dotenv import load_dotenv
from domains import is_amazon_or_aggregator
import argparse

from database import init_db, get_next_pending, mark_status, add_to_queue, update_job_data
from image_composer import compose_image
from api_client import push_to_production, create_job, update_job
import requests
from utils import clean_amazon_url

load_dotenv(override=True)

async def process_queue():
    """Processes items in the local SQLite queue."""
    worker_mode = os.getenv("WORKER_MODE", "server")
    print(f"Checking queue for pending deals... (Mode: {worker_mode})")
    while True:
        deal_item = get_next_pending(worker_mode)
        
        if not deal_item:
            await asyncio.sleep(5) # Wait before checking again
            continue
            
        url = deal_item['url']
        job_type = deal_item.get('type', 'ingestion')
        
        # Ensure we are using the fully cleaned URL
        url = clean_amazon_url(url, resolve_redirects=False) # Already resolved in telegram_scraper, but enforce clean format
        
        item_id = deal_item['id']
        print(f"Processing ID {item_id}: {url}")
        
        print(f"[{deal_item['id']}] Processing {deal_item['type']} job for: {url}")
        
        job_id = create_job(f"Scrape Deal: {url}", deal_item['type'])
        

        job_logs = []
        def add_log(msg):
            print(msg)
            job_logs.append(msg)
            update_job(job_id, logs=[msg])
            
        try:
            # --- PHASE 1: EXTRACTION via PIPELINE ---
            if worker_mode == "desktop" and deal_item['status'] == 'needs_desktop_processing':
                add_log("Desktop Worker executing physical browser extraction...")
                from pipeline import ScrapingPipeline
                deal = await asyncio.to_thread(ScrapingPipeline.process_url, url, "dashboard")
                update_job_data(item_id, deal.model_dump_json(), 'ready_for_publish')
                add_log("Extraction complete. Handing job back to Server for publishing.")
                continue
            elif worker_mode == "server" and deal_item['status'] == 'ready_for_publish':
                add_log("Loading extracted deal data provided by Desktop Worker...")
                from models import Deal
                deal = Deal.model_validate_json(deal_item['data'])
            else:
                add_log("Starting standard pipeline extraction...")
                from pipeline import ScrapingPipeline
                deal = await asyncio.to_thread(ScrapingPipeline.process_url, url, "dashboard")
                
            update_job(job_id, type=f"ingestion ({deal.merchant})")
            
            # --- FAST PATH FOR REAL-TIME PRICE UPDATES ---
            if job_type == 'price_update':
                add_log(f"Fast path for price update: {deal.canonical_url}")
                if deal.price:
                    from api_client import get_api_config
                    backend_url, _ = get_api_config()
                    import requests
                    requests.post(
                        f"{backend_url}/deals/update-price",
                        json={"url": deal.canonical_url, "price": deal.price},
                        headers={"Accept": "application/json"}
                    )
                mark_status(item_id, 'completed')
                update_job(job_id, status="success")
                continue
            # ---------------------------------------------
            
            # 2. Build AI Caption (Using Deal Object)
            add_log("Building Deal Payload...")
            caption_text = deal.ai_caption or f"🚨 {deal.title} \n\n"
            if deal.coupon:
                caption_text += f"\n✂️ Coupon: {deal.coupon}"
            caption_text += f"\n\n👉🏻 Buy Now: {deal.affiliate_url or deal.canonical_url}"
            
            # 3. Create Composite Image
            add_log("Composing deal image...")
            from image_composer import compose_image
            base64_image = compose_image(deal.image_url or '', deal.original_price or 0, deal.price or 0)
            
            # 4. Construct Final Payload
            payload = {
                "title": deal.title,
                "original_price": deal.original_price or 0,
                "discounted_price": deal.price or 0,
                "url": deal.affiliate_url or deal.canonical_url,
                "category_id": deal.category.id if (deal.category and hasattr(deal.category, 'id')) else 1, # Hardcoded fallback for production API
                "category_name": deal.category.name if deal.category else "Electronics",
                "ai_caption": caption_text,
                "features": [],
                "brand": deal.brand or deal.merchant,
                "image_base64": base64_image,
                "ai_score": deal.ai_score or 85
            }
            
            # 5. Push to Laravel
            add_log("Pushing final payload to Laravel...")
            from api_client import push_to_production
            success = push_to_production(payload)
            
            if success:
                mark_status(item_id, 'completed')
                add_log("Deal successfully pushed and saved!")
                update_job(job_id, status="success")
            else:
                mark_status(item_id, 'failed')
                add_log("Failed to push to Laravel API.")
                update_job(job_id, status="failure")
                
        except Exception as e:
            if "CAPTCHA" in str(e) or "needs_desktop_processing" in str(e):
                add_log("Delegating job to Desktop Worker due to CAPTCHA/bot block.")
                mark_status(item_id, 'needs_desktop_processing')
                update_job(job_id, logs=job_logs + ["Delegated to Desktop Worker"])
            else:
                add_log(f"Fatal error processing {url}: {e}")
                mark_status(item_id, 'failed')
                update_job(job_id, status="failure")
            
        # Delay before next item to protect home IP
        await asyncio.sleep(5)

async def expiry_checker():
    """Continuously checks active deals to see if they've expired."""
    print("Starting Expiry Checker Loop...")
    while True:
        try:
            job_id = create_job("Expiry Check Run", "expiry_check")
            logs = []
            def add_log(msg):
                print(msg)
                logs.append(msg)
                update_job(job_id, logs=[msg])
                
            add_log("Fetching active deals from backend...")
            # Fetch active deals from the backend
            backend_url = os.getenv("API_URL", "http://localhost:8000/api/v1")
            api_key = os.getenv("API_KEY", "your-secret-token")
            
            # Assuming an endpoint GET /api/v1/deals/active exists
            response = requests.get(
                f"{backend_url}/deals/active",
                headers={"Authorization": f"Bearer {api_key}", "Accept": "application/json"}
            )
            
            expired_count = 0
            if response.status_code == 200:
                active_deals = response.json().get('deals', [])
                add_log(f"Found {len(active_deals)} active deals to check.")
                for deal in active_deals:
                    add_log(f"Checking expiry for deal: {deal['url']}")
                    # Re-scrape
                    from pipeline import ScrapingPipeline
                    from models import ScraperException
                    
                    is_expired = False
                    try:
                        scraped_deal = await asyncio.to_thread(ScrapingPipeline.process_url, deal['url'], "expiry_check")
                        
                        # Basic Expiry Logic: if title says 'currently unavailable' or price isn't found
                        if "currently unavailable" in (scraped_deal.title or "").lower():
                            is_expired = True
                        elif not scraped_deal.price:
                            is_expired = True
                            
                    except ScraperException as e:
                        if "nodeal" in str(e).lower() or "page not found" in str(e).lower():
                            is_expired = True
                        else:
                            add_log(f"Warning: Scraper failed during expiry check: {e}")
                    except Exception as e:
                        add_log(f"Warning: Scraper failed during expiry check: {e}")
                        
                    if is_expired:
                        add_log(f"🚨 Deal Expired: {deal['url']}. Notifying backend...")
                        requests.post(
                            f"{backend_url}/deals/{deal['id']}/expire",
                            headers={"Authorization": f"Bearer {api_key}", "Accept": "application/json"}
                        )
                        expired_count += 1
                    
                    # Sleep to avoid banning IP during expiry checks
                    await asyncio.sleep(10)
                add_log(f"Completed expiry check. Removed {expired_count} expired deals.")
                update_job(job_id, status="success")
            else:
                add_log("Failed to fetch active deals.")
                update_job(job_id, status="failure")
        except Exception as e:
            if 'job_id' in locals():
                update_job(job_id, logs=[f"Expiry checker error: {e}"], status="failure")
            print(f"Expiry checker error: {e}")
            
        # Run the full expiry check loop every hour
        await asyncio.sleep(3600)

async def listen_to_websockets():
    """Listens to Laravel Reverb WebSockets for instant scrape requests."""
    websocket_url = os.getenv("WEBSOCKET_URL", "ws://localhost:8080/app/my-app-key")
    print(f"Connecting to WebSocket: {websocket_url}")
    
    while True:
        try:
            async with websockets.connect(websocket_url) as websocket:
                print("WebSocket Connected! Subscribing to private-scraper-worker...")
                
                # Pusher/Reverb Subscribe protocol
                subscribe_msg = {
                    "event": "pusher:subscribe",
                    "data": {
                        "channel": "private-scraper-worker"
                    }
                }
                await websocket.send(json.dumps(subscribe_msg))
                
                while True:
                    message = await websocket.recv()
                    data = json.loads(message)
                    
                    if data.get('event') == 'App\\Events\\DealScrapeRequested':
                        payload = json.loads(data.get('data', '{}'))
                        url = payload.get('url')
                        job_type = payload.get('type', 'ingestion')
                        print(f"⚡ Received instant scrape request via WS: {url} ({job_type})")
                        add_to_queue(url, job_type)
                        
                    elif data.get('event') == 'App\\Events\\HuntRequested':
                        payload = json.loads(data.get('data', '{}'))
                        keyword = payload.get('keyword')
                        print(f"⚡ Received instant hunt request via WS for keyword: {keyword}")
                        import subprocess
                        import sys
                        subprocess.Popen([sys.executable, "-u", "hunter.py", "--keyword", str(keyword)])
                        
        except websockets.exceptions.ConnectionClosed:
            print("WebSocket connection closed. Reconnecting in 5s...")
            await asyncio.sleep(5)
        except Exception as e:
            print(f"WebSocket error: {e}. Reconnecting in 5s...")
            await asyncio.sleep(5)

async def main():
    env_mode = os.getenv('WORKER_MODE', 'server')
    parser = argparse.ArgumentParser(description='LatestDeal Worker Daemon')
    parser.add_argument('--mode', type=str, default=env_mode, choices=['server', 'desktop'], help='Worker execution mode')
    args = parser.parse_args()
    os.environ['WORKER_MODE'] = args.mode
    
    # Worker mode is set, use .env for API URLs
    if args.mode == 'desktop':
        pass
        
    init_db()
    print(f"Worker Initialized in {args.mode.upper()} mode.")
    
    if args.mode == 'server':
        await asyncio.gather(
            process_queue(),
            expiry_checker(),
            listen_to_websockets()
        )
    else:
        # Desktop mode only processes the queue
        await asyncio.gather(
            process_queue()
        )

if __name__ == "__main__":
    asyncio.run(main())
