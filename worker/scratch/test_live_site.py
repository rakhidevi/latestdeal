import requests
import json
import re
import urllib3
from bs4 import BeautifulSoup

urllib3.disable_warnings()

headers = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
}

def check_url(url, label="URL"):
    print(f"\n==========================================")
    print(f"Checking {label}: {url}")
    print(f"==========================================")
    try:
        r = requests.get(url, headers=headers, verify=False, timeout=15)
        print(f"Status Code: {r.status_code}")
        print(f"Response Size: {len(r.content)} bytes")
        
        soup = BeautifulSoup(r.text, 'html.parser')
        title = soup.find('title')
        print(f"Title: {title.text if title else 'No Title'}")
        
        # Meta description
        meta_desc = soup.find('meta', attrs={'name': 'description'})
        print(f"Meta Description: {meta_desc.get('content') if meta_desc else 'None'}")
        
        # Canonical link
        canonical = soup.find('link', rel='canonical')
        print(f"Canonical URL: {canonical.get('href') if canonical else 'None'}")
        
        # JSON-LD Scripts
        scripts = soup.find_all('script', type='application/ld+json')
        print(f"JSON-LD blocks found: {len(scripts)}")
        for idx, s in enumerate(scripts):
            raw = s.string or s.text
            try:
                parsed = json.loads(raw)
                print(f"  [+] Block {idx}: VALID JSON-LD ({parsed.get('@type') if isinstance(parsed, dict) else [p.get('@type') for p in parsed if isinstance(p, dict)]})")
            except Exception as e:
                print(f"  [-] Block {idx}: INVALID JSON-LD! Error: {e}")
                print(f"      Snippet: {raw[:150]}...")
                
        # Check for broken images on page
        images = soup.find_all('img')
        broken_imgs = []
        lazy_or_missing = 0
        for img in images[:30]:
            src = img.get('src') or img.get('data-src')
            if not src:
                lazy_or_missing += 1
            elif src.startswith('http'):
                pass # checked externally if needed
        print(f"Total Images: {len(images)} (Empty/Lazy src: {lazy_or_missing})")
        
        # Check H1 tags
        h1s = soup.find_all('h1')
        print(f"H1 Tags ({len(h1s)}): {[h.text.strip()[:40] for h in h1s]}")
        
        return soup, r.text
    except Exception as e:
        print(f"Failed to fetch {url}: {e}")
        return None, None

# 1. Check Homepage
soup_home, html_home = check_url("https://latestdeal.in", "Homepage")

# 2. Extract Deal Links from Homepage
if soup_home:
    deal_links = []
    for a in soup_home.find_all('a', href=True):
        href = a['href']
        if '/deal/' in href:
            if href.startswith('/'):
                href = "https://latestdeal.in" + href
            deal_links.append(href)
    deal_links = list(set(deal_links))
    print(f"\nExtracted {len(deal_links)} deal links from homepage.")
    if deal_links:
        print("First 3 deals:", deal_links[:3])
        # Test first deal
        check_url(deal_links[0], "Product Deal Page")

# 3. Check Key Standard Pages
pages_to_test = [
    ("https://latestdeal.in/categories", "Categories Hub"),
    ("https://latestdeal.in/brands", "Brands Hub"),
    ("https://latestdeal.in/about", "About Us"),
    ("https://latestdeal.in/contact", "Contact Us"),
    ("https://latestdeal.in/privacy", "Privacy Policy"),
    ("https://latestdeal.in/terms", "Terms of Service"),
    ("https://latestdeal.in/editorial-policy", "Editorial Policy"),
    ("https://latestdeal.in/cookie-policy", "Cookie Policy"),
    ("https://latestdeal.in/corrections-policy", "Corrections Policy"),
    ("https://latestdeal.in/search?q=shoes", "Search Page"),
]

for url, label in pages_to_test:
    check_url(url, label)
