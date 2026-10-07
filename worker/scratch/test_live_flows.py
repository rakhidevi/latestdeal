import sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')
import requests, urllib3
from bs4 import BeautifulSoup
urllib3.disable_warnings()

headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'}
base = 'https://latestdeal.in'

# Fetch homepage to find live category, brand, and deal links
r = requests.get(base, headers=headers, verify=False)
soup = BeautifulSoup(r.text, 'html.parser')

cat_links = set()
brand_links = set()
deal_links = set()
go_links = set()

for a in soup.find_all('a', href=True):
    h = a['href']
    if '/categories/' in h: cat_links.add(h)
    elif '/brands/' in h: brand_links.add(h)
    elif '/deal/' in h: deal_links.add(h)
    elif '/go/' in h: go_links.add(h)

print(f"Discovered: {len(cat_links)} categories, {len(brand_links)} brands, {len(deal_links)} deals, {len(go_links)} go links")

# Test first category
if cat_links:
    cl = list(cat_links)[0]
    full_cl = cl if cl.startswith('http') else base + cl
    rc = requests.get(full_cl, headers=headers, verify=False)
    print(f"\nTesting Category: {full_cl}")
    print(f"Status: {rc.status_code}")
    soupc = BeautifulSoup(rc.text, 'html.parser')
    print("Title:", soupc.find('title').text if soupc.find('title') else 'No title')
    print("Deals on category page:", len(soupc.find_all('article')) or len(soupc.find_all('div', class_=lambda c: c and 'deal' in c.lower())))

# Test first brand
if brand_links:
    bl = list(brand_links)[0]
    full_bl = bl if bl.startswith('http') else base + bl
    rb = requests.get(full_bl, headers=headers, verify=False)
    print(f"\nTesting Brand: {full_bl}")
    print(f"Status: {rb.status_code}")
    soupb = BeautifulSoup(rb.text, 'html.parser')
    print("Title:", soupb.find('title').text if soupb.find('title') else 'No title')

# Test deal page and its Outbound Go link
if deal_links:
    dl = list(deal_links)[0]
    full_dl = dl if dl.startswith('http') else base + dl
    rd = requests.get(full_dl, headers=headers, verify=False)
    print(f"\nTesting Deal: {full_dl}")
    print(f"Status: {rd.status_code}")
    soupd = BeautifulSoup(rd.text, 'html.parser')
    # Find Buy Now button
    buy_btns = [a['href'] for a in soupd.find_all('a', href=True) if '/go/' in a['href']]
    print("Outbound Go link found on deal page:", buy_btns)
    if buy_btns:
        go_url = buy_btns[0] if buy_btns[0].startswith('http') else base + buy_btns[0]
        # Test redirect
        r_go = requests.get(go_url, headers=headers, allow_redirects=False, verify=False)
        print(f"Testing Redirect {go_url}:")
        print(f"Status: {r_go.status_code} (Expected 302/301)")
        loc = r_go.headers.get('Location', 'No Location Header')
        print(f"Redirect Location: {loc[:100]}...")
        # Check if affiliate tag is in location
        has_tag = 'tag=' in loc or 'kridaymart' in loc or 'amzn.to' in loc
        print(f"Affiliate Tag Present in Target: {has_tag}")
