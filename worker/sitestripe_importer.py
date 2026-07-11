import sys
import io
import os
import re

if sys.stdout.encoding.lower() != 'utf-8':
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace', line_buffering=True)

from dotenv import load_dotenv
from sitestripe_scraper import get_sitestripe_link_and_data
from ai_agent import generate_caption
from image_composer import compose_image
from api_client import push_to_production
from deal_evaluator import evaluate_deal

def clean_price(val):
    if not val: return 0
    cleaned = re.sub(r'[^\d.]', '', str(val))
    try: return float(cleaned)
    except: return 0

def import_sitestripe_deal(url: str):
    print(f"\n--- Starting SiteStripe Import for: {url} ---")
    load_dotenv(override=True)
    
    try:
        # 1. Scrape with Authenticated Profile
        raw_data = get_sitestripe_link_and_data(url)
        official_shortlink = raw_data.get('sitestripe_url')
        
        if not official_shortlink:
            print("Failed to get official shortlink! Aborting.")
            return False
            
        print(f"Verified Shortlink: {official_shortlink}")
        
        # 2. Evaluate Deal
        print("Evaluating deal metrics...")
        evaluation = evaluate_deal(raw_data)
        if not evaluation["is_approved"]:
            print(f"Deal REJECTED: {evaluation['reason']}")
            # Note: Depending on user preference, we might want to bypass rejection for manual imports
            # But let's stick to the pipeline
            return False

        metrics = evaluation["metrics"]
        raw_data["metrics"] = metrics

        # 3. Generate AI Caption
        print("Generating AI Caption...")
        try:
            caption_data = generate_caption(raw_data, os.getenv("OLLAMA_BASE_URL", "http://localhost:11434"))
            
            caption_data['original_price'] = clean_price(caption_data.get('original_price'))
            caption_data['discounted_price'] = clean_price(caption_data.get('discounted_price'))
            
            caption_text = f"🚨 {caption_data['title']} \n\n" + "\n".join(caption_data.get('features', [])) + f"\n\n👉🏻 Buy Now: {official_shortlink}"
        except Exception as ai_e:
            print(f"AI Generation failed: {ai_e}. Using fallback template.")
            caption_data = {
                'title': raw_data.get('raw_title', 'Awesome Deal'),
                'original_price': metrics["original_price"],
                'discounted_price': metrics["discounted_price"]
            }
            caption_text = f"🔥 NEW DEAL 🔥\n{caption_data['title']}\n\n{caption_data.get('trust_metrics', '')}\n\n💰 Price: {caption_data['discounted_price']} (Was {caption_data['original_price']})\n\nGrab it here: {official_shortlink}"
        
        # 4. Compose Image
        print("Composing deal image...")
        base64_image = compose_image(raw_data.get('image_url', ''), caption_data['original_price'], caption_data['discounted_price'])
        
        # 5. Push to Laravel
        payload = {
            "title": caption_data['title'],
            "original_price": caption_data['original_price'],
            "discounted_price": caption_data['discounted_price'],
            "url": url, # Original URL for deduplication
            "short_url": official_shortlink, # Sitestripe Link
            "category_id": 1,
            "ai_caption": caption_text,
            "features": caption_data.get('features', []),
            "verdict": caption_data.get('verdict', ''),
            "trust_metrics": caption_data.get('trust_metrics', ''),
            "ai_score": caption_data.get('ai_score', None),
            "image_base64": base64_image,
            "brand": caption_data.get('brand_name'),
            "scraper_type": raw_data.get('scraper_type', 'SiteStripe Automation')
        }
        
        print("Pushing final payload to Laravel...")
        success = push_to_production(payload)
        
        if success:
            print("Deal successfully pushed and saved!")
            return True
        else:
            print("Failed to push to Laravel API.")
            return False
            
    except Exception as e:
        print(f"Fatal error importing deal: {e}")
        return False

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Usage: python sitestripe_importer.py <amazon-url>")
        sys.exit(1)
        
    import_sitestripe_deal(sys.argv[1])
