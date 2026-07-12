import os
import re
import json
import asyncio
from telethon import TelegramClient, events
from dotenv import load_dotenv
from pydantic import BaseModel, Field, ValidationError
from openai import OpenAI
import sqlite3
from database import DB_PATH
from telethon import utils

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
# Let's listen to all incoming messages from any chat/channel the account is joined to.
TARGET_CHANNELS = []

client = TelegramClient(SESSION_NAME, API_ID, API_HASH)

# Lock to ensure only one headed Playwright instance opens at a time
sitestripe_lock = asyncio.Lock()

class TelegramDealSchema(BaseModel):
    title: str = Field(description="The product title")
    url: str = Field(description="The affiliate or product URL in the message")
    original_price: float = Field(default=0, description="The MRP or original price")
    discounted_price: float = Field(default=0, description="The final discounted deal price")
    features: list[str] = Field(default=[], description="Bullet points highlighting key features")
    promo_code: str = Field(default="", description="The promo or coupon code if one is available")
    bank_offer: str = Field(default="", description="Any bank discount mentioned")
    store: str = Field(default="Amazon", description="The store name (Amazon, Flipkart, Myntra, etc.)")
    is_deal: bool = Field(description="Set to true if this message actually contains a product deal. Set to false if it's just spam, a greeting, or announcement.")
    ai_score: int = Field(default=85, description="Score this deal out of 100 based on price drop, brand value, and features (e.g., 75-99).")

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
        return None

def check_if_automated_crawler_enabled():
    """Checks the live backend API to see if the automated crawler is active."""
    try:
        import requests
        # Using production URL so it works seamlessly
        response = requests.get('https://latestdeal.in/api/settings/crawlers', timeout=5)
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
    
    # 1. Pre-extract URL and run deep scraping if it's Amazon
    url = ""
    urls = re.findall(r'(https?://[^\s]+)', message_text)
    if urls:
        raw_url = urls[0]
        print(f"Cleaning and Unshortening URL: {raw_url}")
        url = await asyncio.to_thread(clean_amazon_url, raw_url)
    scraped_data = None
    if url and ('amazon' in url.lower() or 'amzn' in url.lower()):
        print(f"Detected Amazon link! Initiating deep Playwright scraping...")
        try:
            from sitestripe_scraper import get_sitestripe_link_and_data
            async with sitestripe_lock:
                scraped_data = await asyncio.to_thread(get_sitestripe_link_and_data, url)
        except Exception as e:
            print(f"Deep scraping failed, falling back to basic parsing: {e}")
            
    is_udemy = False
    if url and ('udemy.com' in url.lower()):
        print(f"Detected Udemy link! Applying Impact affiliate tracking parameters...")
        is_udemy = True
        try:
            parsed = urllib.parse.urlparse(url)
            query_params = urllib.parse.parse_qs(parsed.query)
            
            # Add user's specific impact parameters
            impact_params = {
                'im_ref': ['3UDwqRybsxyZWDu1FDz21SsZUkuVtBwg7TRYz00'],
                'irpid': ['7475040'],
                'utm_medium': ['affiliate'],
                'utm_source': ['impact'],
                'utm_audience': ['mx'],
                'utm_tactic': ['"APAC","Coupon/Deal"'],
                'utm_content': ['3193860'],
                'utm_campaign': ['7475040'],
                'irgwc': ['1'],
                'afsrc': ['1']
            }
            # Merge while keeping existing parameters (like couponCode) if not overriding
            query_params.update(impact_params)
            
            # Reconstruct the URL
            new_query = urllib.parse.urlencode(query_params, doseq=True)
            url = urllib.parse.urlunparse(
                (parsed.scheme, parsed.netloc, parsed.path, parsed.params, new_query, parsed.fragment)
            )
            print(f"Generated Udemy Affiliate Link: {url}")
        except Exception as e:
            print(f"Failed to append Udemy tracking params: {e}")
            
    # 2. Parse Message with LLM (enriched with scraped_data)
    print("Parsing message with LLM...")
    deal_data = await asyncio.to_thread(parse_telegram_message, message_text, "http://localhost:11434", scraped_data)
    
    if not deal_data or not deal_data.get('is_deal'):
        print("Not a valid deal or parsing failed. Skipping.")
        return
        
    # Override URL with the LLM extracted one if pre-extraction failed, or with sitestripe shortlink
    if not url:
        url = deal_data.get('url')
        if url:
            url = await asyncio.to_thread(expand_url, url)
            
    if scraped_data and scraped_data.get('sitestripe_url'):
        url = scraped_data['sitestripe_url']
        print(f"Overriding with generated affiliate link: {url}")
        
    if not url:
        print("No URL found in deal. Skipping.")
        return

    print(f"✅ Extracted Deal: {deal_data['title']} (₹{deal_data['discounted_price']})")
            
    # 3. Build AI Caption using the extra Telegram info
    caption_text = f"🚨 {deal_data['title']} \n\n"
    if deal_data.get('bank_offer'):
        caption_text += f"💳 Bank Offer: {deal_data['bank_offer']}\n"
    if deal_data.get('promo_code'):
        caption_text += f"✂️ Coupon: {deal_data['promo_code']}\n"
    caption_text += f"\n👉🏻 Buy Now: {url}"

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
        og_url = ""
        if scraped_data and scraped_data.get('image_url'):
            og_url = scraped_data['image_url']
        else:
            og_url = await asyncio.to_thread(fetch_og_image, url)
        
        # Use fetched image or dummy gradient placeholder
        placeholder_url = og_url if og_url else "https://placehold.co/800x800/e2e8f0/475569.png?text=Loot+Deal"
        print(f"Generating deal card with image: {placeholder_url}")
        
        image_base64 = await asyncio.to_thread(
            compose_image, 
            placeholder_url, 
            deal_data['original_price'], 
            deal_data['discounted_price']
        )

    # 5. Construct Final Payload
    payload = {
        "title": deal_data['title'],
        "original_price": deal_data['original_price'],
        "discounted_price": deal_data['discounted_price'],
        "url": url,
        "category_id": None, # Nullable in API now
        "category_name": "Courses" if is_udemy else None, # Let API auto-resolve
        "ai_caption": caption_text,
        "features": deal_data.get('features', []),
        "brand": deal_data.get('store', 'Unknown'),
        "image_base64": image_base64,
        "ai_score": deal_data.get('ai_score', 85)
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
