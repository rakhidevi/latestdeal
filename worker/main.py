import sys
import io

if sys.stdout.encoding.lower() != 'utf-8':
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')
if sys.stderr.encoding.lower() != 'utf-8':
    sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding='utf-8', errors='replace')

import time
import os
import asyncio
import json
import websockets
from dotenv import load_dotenv

from database import init_db, get_next_pending, mark_status, add_to_queue
from scraper import extract_deal_data
from ai_agent import generate_caption
from image_composer import compose_image
from api_client import push_to_production
from deal_evaluator import evaluate_deal
import requests

load_dotenv()

async def process_queue():
    """Processes items in the local SQLite queue."""
    print("Checking queue for pending deals...")
    while True:
        deal_item = get_next_pending()
        
        if not deal_item:
            await asyncio.sleep(5) # Wait before checking again
            continue
            
        url = deal_item['url']
        item_id = deal_item['id']
        print(f"Processing ID {item_id}: {url}")
        
        try:
            # 1. Scrape the URL
            raw_data = await asyncio.to_thread(extract_deal_data, url)
            
            import re
            def clean_price(val):
                if not val: return 0
                cleaned = re.sub(r'[^\d.]', '', str(val))
                try: return float(cleaned)
                except: return 0

            # 1.5 Evaluate Deal
            evaluation = evaluate_deal(raw_data)
            if not evaluation["is_approved"]:
                print(f"Deal {url} REJECTED by Evaluator. Reason: {evaluation['reason']}")
                mark_status(item_id, 'failed')
                continue
                
            metrics = evaluation["metrics"]
            # Inject metrics into raw_data for AI caption generator
            raw_data["metrics"] = metrics

            # 2. Generate Caption via Ollama
            try:
                caption_data = generate_caption(raw_data, os.getenv("OLLAMA_BASE_URL", "http://localhost:11434"))
                
                # Sanitize prices returned by AI or fallback
                caption_data['original_price'] = clean_price(caption_data.get('original_price'))
                caption_data['discounted_price'] = clean_price(caption_data.get('discounted_price'))
                
                affiliate_url = url
                if "amazon" in url:
                    affiliate_url = url + ("&" if "?" in url else "?") + "tag=kridaymart-21"
                
                caption_text = f"🚨 {caption_data['title']} \n\n" + "\n".join(caption_data.get('features', [])) + f"\n\n👉🏻 Buy Now: {affiliate_url}"
            except Exception as ai_e:
                print(f"AI Generation failed: {ai_e}. Using fallback template.")
                caption_data = {
                    'title': raw_data.get('raw_title', 'Awesome Deal'),
                    'original_price': metrics["original_price"],
                    'discounted_price': metrics["discounted_price"]
                }
                
                affiliate_url = url
                if "amazon" in url:
                    affiliate_url = url + ("&" if "?" in url else "?") + "tag=kridaymart-21"
                
                caption_text = f"🔥 NEW DEAL 🔥\n{caption_data['title']}\n\n{caption_data.get('trust_metrics', '')}\n\n💰 Price: {caption_data['discounted_price']} (Was {caption_data['original_price']})\n\nGrab it here: {affiliate_url}"
            
            # 3. Create Composite Image
            base64_image = compose_image(raw_data.get('image_url', ''), caption_data['original_price'], caption_data['discounted_price'])
            
            # 4. Construct Final Payload
            payload = {
                "title": caption_data['title'],
                "original_price": caption_data['original_price'],
                "discounted_price": caption_data['discounted_price'],
                "url": affiliate_url,
                "category_id": 1, # Placeholder
                "merchant_id": 1, # Placeholder
                "ai_caption": caption_text,
                "features": caption_data.get('features', []),
                "verdict": caption_data.get('verdict', ''),
                "trust_metrics": caption_data.get('trust_metrics', ''),
                "image_base64": base64_image,
                "brand": caption_data.get('brand_name')
            }
            
            # 5. Push to Laravel
            success = push_to_production(payload)
            
            if success:
                mark_status(item_id, 'completed')
            else:
                mark_status(item_id, 'failed')
                
        except Exception as e:
            print(f"Failed to process {url}: {e}")
            mark_status(item_id, 'failed')
            
        # Delay before next item to protect home IP
        await asyncio.sleep(5)

async def expiry_checker():
    """Continuously checks active deals to see if they've expired."""
    print("Starting Expiry Checker Loop...")
    while True:
        try:
            # Fetch active deals from the backend
            backend_url = os.getenv("API_URL", "http://localhost:8000/api/v1")
            api_key = os.getenv("API_KEY", "your-secret-token")
            
            # Assuming an endpoint GET /api/v1/deals/active exists
            response = requests.get(
                f"{backend_url}/deals/active",
                headers={"Authorization": f"Bearer {api_key}", "Accept": "application/json"}
            )
            
            if response.status_code == 200:
                active_deals = response.json().get('deals', [])
                for deal in active_deals:
                    print(f"Checking expiry for deal: {deal['url']}")
                    # Re-scrape
                    raw_data = await asyncio.to_thread(extract_deal_data, deal['url'])
                    
                    # Basic Expiry Logic: if title says 'currently unavailable' or price isn't found
                    is_expired = False
                    if "currently unavailable" in str(raw_data.get('raw_title', '')).lower():
                        is_expired = True
                    elif not raw_data.get('raw_discounted_price'):
                        is_expired = True
                        
                    if is_expired:
                        print(f"🚨 Deal Expired: {deal['url']}. Notifying backend...")
                        requests.post(
                            f"{backend_url}/deals/{deal['id']}/expire",
                            headers={"Authorization": f"Bearer {api_key}", "Accept": "application/json"}
                        )
                    
                    # Sleep to avoid banning IP during expiry checks
                    await asyncio.sleep(10)
        except Exception as e:
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
                    
                    # Ignore pusher internal pings
                    if data.get('event') == 'App\\Events\\DealScrapeRequested':
                        payload = json.loads(data.get('data', '{}'))
                        url = payload.get('url')
                        print(f"⚡ Received instant scrape request via WS: {url}")
                        add_to_queue(url)
                        
        except websockets.exceptions.ConnectionClosed:
            print("WebSocket connection closed. Reconnecting in 5s...")
            await asyncio.sleep(5)
        except Exception as e:
            print(f"WebSocket error: {e}. Reconnecting in 5s...")
            await asyncio.sleep(5)

async def main():
    init_db()
    print("Worker Initialized.")
    
    # Run all three tasks concurrently
    await asyncio.gather(
        process_queue(),
        expiry_checker(),
        listen_to_websockets()
    )

if __name__ == "__main__":
    asyncio.run(main())
