import os
import re
import json
import asyncio
import urllib.parse
from telethon import TelegramClient, events
from dotenv import load_dotenv
from pydantic import BaseModel, Field, ValidationError
from typing import Optional
from openai import OpenAI
import sqlite3
from database import DB_PATH
from telethon import utils
from domains import is_amazon_or_aggregator

# Import existing logic to push deals
from api_client import push_to_production
from utils import clean_amazon_url
from image_composer import compose_image

load_dotenv()

# We can store these in .env later, but using the provided keys for now
API_ID = '32985684'
API_HASH = '2b3f3db5b0f54fa1e65633266d48bff8'
SESSION_NAME = 'latestdeal_telegram'

# The channels we want to listen to. Use empty list to listen to ALL joined channels, or specify valid @usernames.
from config_manager import config
TARGET_CHANNELS = config.get("telegram", {}).get("target_channels", [])

client = TelegramClient(SESSION_NAME, API_ID, API_HASH)

# Lock to ensure only one headed Playwright instance opens at a time
sitestripe_lock = asyncio.Lock()

class TelegramDealSchema(BaseModel):
    title: str = Field(description="The product title")
    url: str = Field(description="The affiliate or product URL in the message")
    original_price: Optional[float] = Field(default=0, description="The MRP or original price")
    discounted_price: Optional[float] = Field(default=0, description="The final discounted deal price")
    features: list[str] = Field(default=[], description="Bullet points highlighting key features")
    promo_code: str = Field(default="", description="The promo or coupon code if one is available")
    bank_offer: str = Field(default="", description="Any bank discount mentioned")
    store: str = Field(default="Amazon", description="The store name (Amazon, Flipkart, Myntra, etc.)")
    is_deal: bool = Field(default=True, description="Set to true if this message actually contains a product deal. Set to false if it's just spam, a greeting, or announcement.")
    ai_score: Optional[int] = Field(default=85, description="Score this deal out of 100 based on price drop, brand value, and features (e.g., 75-99).")

def fetch_og_image(url: str) -> str:
    """Attempts to fetch the og:image or main product image from the target URL."""
    try:
        import requests
        from bs4 import BeautifulSoup
        headers = {"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"}
        response = requests.get(url, headers=headers, timeout=5)
        soup = BeautifulSoup(response.text, 'html.parser')
        
        # Check standard open graph image
        og_image = soup.find('meta', property='og:image')
        if og_image and og_image.get('content'):
            return og_image['content']
            
        # Amazon-specific fallback (landingImage)
        landing_img = soup.find('img', id='landingImage')
        if landing_img:
            if landing_img.get('data-old-hires'):
                return landing_img['data-old-hires']
            if landing_img.get('src'):
                return landing_img['src']
    except Exception as e:
        print(f"Error fetching og:image: {e}")
    return ""

def expand_url(url: str) -> str:
    """Follows redirects to find the final URL (unshortens bit.ly, amzn.to, etc)."""
    try:
        import requests
        headers = {"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"}
        response = requests.head(url, allow_redirects=True, headers=headers, timeout=10)
        if response.status_code >= 400:
            response = requests.get(url, allow_redirects=True, headers=headers, timeout=10)
        return response.url
    except Exception as e:
        print(f"Error expanding URL {url}: {e}")
        return url

def parse_telegram_message(message_text: str, ollama_url: str = "http://localhost:11434", scraped_data: dict = None) -> dict:
    """Uses Ollama to parse a raw Telegram deal message into structured JSON."""
    llm_client = OpenAI(
        base_url=f"{ollama_url}/v1",
        api_key="ollama" 
    )
    
    extra_context = ""
    if scraped_data:
        extra_context = f"""
        Additionally, we deeply scraped the product page. Use this FACTUAL data to improve your extraction (especially features, real price, title):
        - Real Title: {scraped_data.get('raw_title', 'Unknown')}
        - Features: {scraped_data.get('features', [])}
        - Rating: {scraped_data.get('star_rating', 'Unknown')} ({scraped_data.get('review_count', 'Unknown')} reviews)
        - Original Price: {scraped_data.get('raw_original_price', 'Unknown')}
        - Discounted Price: {scraped_data.get('raw_discounted_price', 'Unknown')}
        """
    
    prompt = f"""
    You are an expert affiliate marketer. Parse this raw Telegram deal message into a structured JSON object.
    You MUST output valid JSON matching this schema:
    {TelegramDealSchema.model_json_schema()}
    
    IMPORTANT: You must output a valid JSON INSTANCE populated with the extracted data. 
    DO NOT output the JSON Schema definition itself. DO NOT use "properties" as the root key.
    
    RAW MESSAGE:
    {message_text}
    
    {extra_context}
    """
    
    try:
        response = llm_client.chat.completions.create(
            model="qwen2.5-coder:7b", # fallback to llama3.1 if needed
            response_format={"type": "json_object"},
            messages=[
                {"role": "system", "content": "You are a helpful assistant that outputs strictly in JSON format."},
                {"role": "user", "content": prompt}
            ]
        )
        parsed_data = json.loads(response.choices[0].message.content)
        validated_deal = TelegramDealSchema(**parsed_data)
        return validated_deal.model_dump()
    except Exception as e:
        print(f"LLM Parsing failed: {e}")
        # Fallback if LLM fails
        if scraped_data:
            print("Using scraped_data as fallback for LLM.")
            return {
                "title": scraped_data.get("raw_title", "Deal extracted from Telegram"),
                "url": scraped_data.get("url", ""),
                "original_price": float(re.sub(r'[^\d.]', '', str(scraped_data.get("raw_original_price", "0"))) or 0),
                "discounted_price": float(re.sub(r'[^\d.]', '', str(scraped_data.get("raw_discounted_price", "0"))) or 0),
                "features": scraped_data.get("features", []),
                "promo_code": "",
                "bank_offer": "",
                "store": "Amazon",
                "is_deal": True,
                "ai_score": 85
            }
        
        # Regex fallback
        print("Using Regex fallback for LLM.")
        urls = re.findall(r'(https?://[^\s]+)', message_text)
        url = urls[0] if urls else ""
        if url:
            return {
                "title": message_text.split('\n')[0][:100],
                "url": url,
                "original_price": 0,
                "discounted_price": 0,
                "features": [],
                "promo_code": "",
                "bank_offer": "",
                "store": "Unknown",
                "is_deal": True,
                "ai_score": 85
            }
        return None

def check_if_automated_crawler_enabled():
    """Checks the live backend API to see if the automated crawler is active."""
    try:
        import requests
        from api_client import get_api_config
        api_url, _ = get_api_config()
        # Remove /api suffix if present to append /settings/crawlers cleanly, or just handle it
        base_url = api_url.replace('/api', '') if api_url.endswith('/api') else api_url
        response = requests.get(f'{base_url}/api/settings/crawlers', timeout=5)
        if response.status_code == 200:
            return response.json().get('crawler_automated') == 'enabled'
    except Exception as e:
        print(f"Error checking crawler settings: {e}")
    # Default to True if API is unreachable
    return True

@client.on(events.NewMessage())
async def handler(event):
    if not check_if_automated_crawler_enabled():
        return
        
    message_text = event.message.message
    if not message_text:
        return
        
    chat_name = utils.get_display_name(event.chat) if event.chat else "Unknown Chat"
    print(f"\n[{chat_name}] New Message Received!")
    
    # 0. Filter out Spam / Pirated Content early to save resources
    blocked_keywords = [
        'mod apk', 'modded apk', 'cracked apk',
        'premium unlocked', 'unlocked all', 'pro unlocked',
        'no watermark', 'ad free mod', 'ads removed mod',
        'crack', 'cracked', 'keygen', 'serial key',
        'pirated', 'warez', 'nulled',
        'paid apk free', 'patched apk',
    ]
    message_lower = message_text.lower()
    for kw in blocked_keywords:
        if kw in message_lower:
            print(f"🚫 Blocked Keyword Detected ('{kw}'). Ignoring illegal/spam deal.")
            return
    
    # 1. Pre-extract URL and run generic pipeline
    url = ""
    urls = re.findall(r'(https?://[^\s]+)', message_text)
    if urls:
        raw_url = urls[0]
        
        # Check against ignored domains in config
        from config_manager import config
        ignore_domains = config.get("scraper", {}).get("ignore_domains", [])
        if any(ignored in raw_url for ignored in ignore_domains):
            print(f"🚫 URL matched ignore list: {raw_url}. Skipping.")
            return
            
        try:
            from pipeline import ScrapingPipeline
            # Retry loop for resilient scraping
            max_retries = 2
            deal = None
            for attempt in range(max_retries):
                try:
                    deal = await asyncio.to_thread(ScrapingPipeline.process_url, raw_url, "telegram")
                    break
                except Exception as e:
                    print(f"Pipeline attempt {attempt+1} failed for {raw_url}: {e}")
                    if attempt == max_retries - 1:
                        return
                    await asyncio.sleep(2)
        except Exception as e:
            print(f"Failed to load pipeline: {e}")
            return
            
    # 2. Extract extra text from Telegram message using LLM to find Promo Codes/Bank Offers
    # Since the pipeline already got the core product info (Title, Price, etc), we only need 
    # to supplement it with Telegram-specific context (like coupon codes or bank offers).
    print("Parsing message with LLM for supplementary Telegram context...")
    deal_data = await asyncio.to_thread(parse_telegram_message, message_text, "http://localhost:11434")
    
    if deal_data and not deal_data.get('is_deal'):
        print("Not a valid deal or parsing failed. Skipping.")
        return
        
    if not url and deal_data:
        # Fallback if regex missed it but LLM found it
        raw_url = deal_data.get('url')
        if raw_url:
            try:
                from pipeline import ScrapingPipeline
                max_retries = 2
                for attempt in range(max_retries):
                    try:
                        deal = await asyncio.to_thread(ScrapingPipeline.process_url, raw_url, "telegram")
                        break
                    except Exception as e:
                        print(f"Fallback Pipeline attempt {attempt+1} failed for {raw_url}: {e}")
                        if attempt == max_retries - 1:
                            return
                        await asyncio.sleep(2)
            except Exception as e:
                print(f"Failed to load pipeline: {e}")
                return

    if 'deal' not in locals() or not deal:
        print("No URL found in deal or pipeline completely failed. Skipping.")
        return

    # Merge Telegram context into the Deal object
    if deal_data:
        if not deal.coupon and deal_data.get('promo_code'):
            deal.coupon = deal_data['promo_code']
        # We can also add bank_offer to features if not present
        if deal_data.get('bank_offer'):
            deal.title = f"[Bank Offer: {deal_data['bank_offer']}] " + deal.title

    print(f"✅ Extracted Deal: {deal.title} (₹{deal.price})")
            
    # 3. Build AI Caption using the extra Telegram info
    caption_text = deal.ai_caption or f"🚨 {deal.title} \n\n"
    if deal.coupon:
        caption_text += f"\n✂️ Coupon: {deal.coupon}"
    caption_text += f"\n\n👉🏻 Buy Now: {deal.affiliate_url or deal.canonical_url}"

    # 4. Handle Image
    # If the telegram message has a photo, let's download it
    image_base64 = ""
    if event.message.photo:
        print("Downloading image from Telegram message...")
        photo_path = await event.message.download_media(file="temp_telegram_img.jpg")
        if photo_path:
            import base64
            with open(photo_path, "rb") as image_file:
                raw_b64 = base64.b64encode(image_file.read()).decode('utf-8')
                image_base64 = f"data:image/jpeg;base64,{raw_b64}"
            os.remove(photo_path)
            
    # Fallback to Composer if no image found in Telegram message
    if not image_base64:
        print("No image in message, attempting to scrape image from URL...")
        og_url = deal.image_url
        if not og_url:
            og_url = await asyncio.to_thread(fetch_og_image, deal.canonical_url)
        
        # Use fetched image or dummy gradient placeholder
        placeholder_url = og_url if og_url else "https://placehold.co/800x800/e2e8f0/475569.png?text=Loot+Deal"
        print(f"Generating deal card with image: {placeholder_url}")
        
        image_base64 = await asyncio.to_thread(
            compose_image, 
            placeholder_url, 
            deal.original_price or 0, 
            deal.price or 0
        )

    # 5. Construct Final Payload
    payload = {
        "title": deal.title,
        "original_price": deal.original_price or 0,
        "discounted_price": deal.price or 0,
        "url": deal.affiliate_url or deal.canonical_url,
        "category_id": deal.category.id if (deal.category and hasattr(deal.category, 'id')) else 1, # Hardcoded fallback for production API
        "category_name": deal.category.name if deal.category else "Electronics",
        "ai_caption": caption_text,
        "features": deal_data.get('features', []) if deal_data else [],
        "brand": deal.brand or deal.merchant,
        "image_base64": image_base64,
        "ai_score": deal.ai_score or 85
    }
            
    # 6. Push to Laravel
    print("Pushing to Laravel Production Database...")
    success = await asyncio.to_thread(push_to_production, payload)
    
    if success:
        print("🚀 Successfully pushed automated Telegram deal!")
    else:
        print("❌ Failed to push to Laravel API.")

async def main():
    print("Starting Automated Telegram Scraper...")
    await client.start()
    print(f"Listening to channels: {TARGET_CHANNELS}")
    print("Waiting for deals...")
    await client.run_until_disconnected()

if __name__ == '__main__':
    asyncio.run(main())
