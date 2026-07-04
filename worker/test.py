import sys
import io

if sys.stdout.encoding.lower() != 'utf-8':
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')
if sys.stderr.encoding.lower() != 'utf-8':
    sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding='utf-8', errors='replace')

import sqlite3
import asyncio
from main import process_queue
import os



print("Running process_queue...")
# Since process_queue has an infinite loop, we can just run one cycle manually or let it run
from database import get_next_pending, mark_status
from scraper import extract_deal_data
from ai_agent import generate_caption
from image_composer import compose_image
from api_client import push_to_production
from deal_evaluator import evaluate_deal

deal_item = get_next_pending()
if deal_item:
    url = deal_item['url']
    item_id = deal_item['id']
    print(f"Processing ID {item_id}: {url}")
    
    try:
        raw_data = extract_deal_data(url)
        print("Scrape data:", raw_data)
        
        import re
        def clean_price(val):
            if not val: return 0
            cleaned = re.sub(r'[^\d.]', '', str(val))
            try: return float(cleaned)
            except: return 0

        evaluation = evaluate_deal(raw_data)
        if not evaluation["is_approved"]:
            print(f"Deal {url} REJECTED by Evaluator. Reason: {evaluation['reason']}")
            conn = sqlite3.connect('state.db')
            conn.execute("UPDATE deals_queue SET status='failed' WHERE id=?", (item_id,))
            conn.commit()
            conn.close()
            import sys; sys.exit(0)
            
        metrics = evaluation["metrics"]
        raw_data["metrics"] = metrics

        affiliate_url = url
        if "amazon" in url:
            affiliate_url = url + ("&" if "?" in url else "?") + "tag=kridaymart-21"

        caption_data = {
            'title': raw_data.get('raw_title', 'Awesome Deal'),
            'original_price': metrics['original_price'],
            'discounted_price': metrics['discounted_price']
        }
        caption_text = f"🔥 NEW DEAL 🔥\n{caption_data['title']}\n\n{caption_data.get('trust_metrics', '')}\n\n💰 Price: {caption_data['discounted_price']} (Was {caption_data['original_price']})\n\nGrab it here: {affiliate_url}"
        
        base64_image = compose_image(raw_data.get('image_url', ''), caption_data['original_price'], caption_data['discounted_price'])
        print("Generated image length:", len(base64_image) if base64_image else 0)
        
        payload = {
            "title": caption_data['title'],
            "original_price": caption_data['original_price'],
            "discounted_price": caption_data['discounted_price'],
            "url": affiliate_url,
            "category_id": 1,
            "merchant_id": 1,
            "ai_caption": caption_text,
            "image_base64": base64_image
        }
        
        success = push_to_production(payload)
        print("Push success:", success)
        
    except Exception as e:
        import traceback
        traceback.print_exc()
        mark_status(item_id, 'failed')
