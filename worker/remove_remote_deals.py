import requests
import os
from dotenv import load_dotenv

load_dotenv(override=True)

API_URL = os.getenv("API_URL", "https://latestdeal.in/api")
API_KEY = os.getenv("API_KEY", "")

headers = {}
if API_KEY:
    headers['Authorization'] = f'Bearer {API_KEY}'

# Fetch all active deals
res = requests.get(f"{API_URL}/deals/active", headers=headers)
if res.status_code != 200:
    print(f"Failed to fetch active deals. Status: {res.status_code}")
    print(res.text)
    exit(1)

deals = res.json().get('deals', [])
print(f"Fetched {len(deals)} active deals from production.")

to_expire = []
for deal in deals:
    url = deal.get('url', '')
    short_url = deal.get('short_url', '')
    
    # Direct match
    if url and 'indiafreestuff.in' in url.lower():
        print(f"Found match in direct url for deal {deal.get('id')}: {url}")
        to_expire.append(deal.get('id'))
        continue
    
    # Redirect check
    urls_to_check = [u for u in [url, short_url] if u]
    found = False
    for u in urls_to_check:
        try:
            r = requests.head(u, allow_redirects=True, timeout=5)
            if 'indiafreestuff.in' in r.url.lower():
                found = True
                print(f"Found match via redirect for deal {deal.get('id')}: {u} -> {r.url}")
                break
        except Exception:
            pass
    
    if found:
        to_expire.append(deal.get('id'))

if not to_expire:
    print("No deals found to expire.")
else:
    for deal_id in to_expire:
        print(f"Expiring deal {deal_id}...")
        exp_res = requests.post(f"{API_URL}/deals/{deal_id}/expire", headers=headers)
        if exp_res.status_code == 200:
            print(f"Deal {deal_id} expired successfully.")
        else:
            print(f"Failed to expire deal {deal_id}: {exp_res.status_code} {exp_res.text}")
