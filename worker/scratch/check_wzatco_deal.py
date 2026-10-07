import sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')
sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding='utf-8', errors='replace')

import requests, urllib3, json
from bs4 import BeautifulSoup
urllib3.disable_warnings()

headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'}
url = "https://latestdeal.in/deal/wzatco-legend-gt-google-tv-official-native-1080p-projector-4k-ultra-hd-2500-ansi-ultra-bright-fully-automatic-sealed-engine-20-watt-smart-home-cinema-black-241"

resp = requests.get(url, headers=headers, verify=False)
print("Status:", resp.status_code)
soup = BeautifulSoup(resp.text, 'html.parser')

print("Title:", soup.find('title').text if soup.find('title') else 'No title')

# Check prices displayed on page
price_elems = soup.find_all(string=lambda t: t and ('₹' in t or 'Rs' in t or 'MRP' in t or 'M.R.P' in t))
print(f"\nPrice snippets on page ({len(price_elems)} found):")
for p in price_elems[:20]:
    parent = p.parent.text.strip().replace('\n', ' ')
    print(" -", parent[:120])

# Check Verify Live Price button or endpoint
verify_btn = soup.find(lambda tag: tag.name in ['button', 'a'] and any(kw in tag.text.lower() for kw in ['verify', 'live price', 'refresh', 'check price']))
if verify_btn:
    print("\nVerify Price Button Found:")
    print(" Tag:", verify_btn.name)
    print(" Text:", verify_btn.text.strip())
    print(" Attrs:", verify_btn.attrs)
else:
    print("\nVerify Price Button NOT found!")

# Check script tags for refreshPrice or API calls
scripts = soup.find_all('script')
refresh_scripts = [s.text for s in scripts if s.text and ('refresh-price' in s.text or 'update-price' in s.text or 'verifyPrice' in s.text or 'checkPrice' in s.text)]
print(f"\nScripts mentioning refresh-price/update-price: {len(refresh_scripts)}")
for s in refresh_scripts:
    print("Snippet:", s[:300].strip())
