import os, sys, time
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
sys.stdout.reconfigure(encoding='utf-8')
from sitestripe_scraper import get_sitestripe_link_and_data

url = "https://www.amazon.in/dp/B0FJYJ8LVY"
print(f"Testing SiteStripe & Rufus Scraper on: {url}")
result = get_sitestripe_link_and_data(url)
print("\n" + "="*50)
print("FINAL SCRAPE RESULT:")
if result:
    print("URL:", result.get("url"))
    print("SiteStripe URL:", result.get("sitestripe_url"))
    print("Title:", result.get("raw_title"))
    print("Price History Data:", result.get("price_history"))
else:
    print("Result was None or False")
print("="*50)
