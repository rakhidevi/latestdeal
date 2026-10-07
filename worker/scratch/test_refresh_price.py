import requests, urllib3
urllib3.disable_warnings()

s = requests.Session()
# First visit the deal page to get session cookies
deal_url = 'https://latestdeal.in/deal/wzatco-legend-gt-google-tv-official-native-1080p-projector-4k-ultra-hd-2500-ansi-ultra-bright-fully-automatic-sealed-engine-20-watt-smart-home-cinema-black-241'
headers = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    'Accept-Language': 'en-US,en;q=0.9',
}
r_deal = s.get(deal_url, headers=headers, verify=False)
print("Deal page status:", r_deal.status_code)
print("Cookies:", s.cookies.get_dict())

headers['Referer'] = deal_url
headers['X-Requested-With'] = 'XMLHttpRequest'
headers['Accept'] = 'application/json, text/plain, */*'

# Try GET /deals/241/refresh-price
r_get = s.get('https://latestdeal.in/deals/241/refresh-price', headers=headers, verify=False)
print("GET /deals/241/refresh-price status:", r_get.status_code)
print("GET response:", r_get.text)

# Try POST /api/deals/241/refresh-price
r_post_api = s.post('https://latestdeal.in/api/deals/241/refresh-price', headers=headers, json={}, verify=False)
print("POST /api/deals/241/refresh-price status:", r_post_api.status_code)
print("POST api response:", r_post_api.text)

# Try POST /deals/241/refresh-price
r_post_web = s.post('https://latestdeal.in/deals/241/refresh-price', headers=headers, json={}, verify=False)
print("POST /deals/241/refresh-price status:", r_post_web.status_code)
print("POST web response:", r_post_web.text)
