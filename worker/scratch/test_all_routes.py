import sys, io, time
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')
import requests, urllib3
from bs4 import BeautifulSoup

urllib3.disable_warnings()

headers = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    'Accept-Language': 'en-US,en;q=0.9',
}

base_url = "https://latestdeal.in"

urls_to_test = [
    ("/", "Homepage"),
    ("/categories", "Categories Hub"),
    ("/brands", "Brands Hub"),
    ("/merchants", "Merchants Hub"),
    ("/guides", "Editorial Buying Guides"),
    ("/assistant", "AI Shopping Assistant"),
    ("/about", "About Us"),
    ("/contact", "Contact Us"),
    ("/privacy", "Privacy Policy"),
    ("/terms", "Terms of Service"),
    ("/cookie-policy", "Cookie Policy"),
    ("/editorial-policy", "Editorial Policy"),
    ("/corrections-policy", "Corrections Policy"),
    ("/how-it-works", "How It Works"),
    ("/affiliate-disclosure", "Affiliate Disclosure"),
    ("/editorial-team", "Editorial Team"),
    ("/sitemap.xml", "XML Sitemap"),
    ("/deals/under-500", "Under ₹500 Deals"),
    ("/deals/under-1000", "Under ₹1,000 Deals"),
    ("/deals/50-off", "50% Off Deals"),
    ("/search?search=samsung", "Search: samsung"),
    ("/search?q=samsung", "Search: q=samsung"),
]

results = []

for path, label in urls_to_test:
    full_url = base_url + path
    start = time.time()
    try:
        r = requests.get(full_url, headers=headers, timeout=15, verify=False)
        dur = round((time.time() - start) * 1000)
        
        soup = BeautifulSoup(r.text, 'html.parser')
        title = soup.find('title')
        title_text = title.text.strip() if title else 'NO TITLE'
        h1 = soup.find('h1')
        h1_text = h1.text.strip() if h1 else 'NO H1'
        
        # Check canonical
        canonical = soup.find('link', rel='canonical')
        can_url = canonical.get('href') if canonical else 'NONE'
        
        # Check meta desc
        desc = soup.find('meta', attrs={'name': 'description'})
        desc_text = desc.get('content') if desc else 'NONE'
        
        # Check for broken links / internal links
        internal_links = [a['href'] for a in soup.find_all('a', href=True) if a['href'].startswith('/') or 'latestdeal.in' in a['href']]
        
        results.append({
            'label': label,
            'path': path,
            'status': r.status_code,
            'duration_ms': dur,
            'size': len(r.content),
            'title': title_text[:50],
            'h1': h1_text[:40],
            'canonical': can_url,
            'has_desc': desc_text != 'NONE',
            'links_count': len(internal_links)
        })
    except Exception as e:
        results.append({
            'label': label,
            'path': path,
            'status': f"ERROR: {str(e)[:30]}",
            'duration_ms': 0,
            'size': 0,
            'title': 'ERROR',
            'h1': 'ERROR',
            'canonical': 'ERROR',
            'has_desc': False,
            'links_count': 0
        })

print("\n" + "="*95)
print(f"{'Label':<25} | {'Path':<22} | {'Status':<6} | {'Time(ms)':<8} | {'Title':<30}")
print("="*95)
for res in results:
    print(f"{res['label']:<25} | {res['path']:<22} | {str(res['status']):<6} | {str(res['duration_ms']):<8} | {res['title']:<30}")
print("="*95)
