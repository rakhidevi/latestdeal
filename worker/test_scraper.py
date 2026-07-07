import asyncio
from scraper import extract_deal_data

url = "https://www.amazon.in/Apple-New-iPhone-12-128GB/dp/B08L5TNJHG/"
data = extract_deal_data(url)
print(data)
