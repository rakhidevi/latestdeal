import asyncio
from pipeline import ScrapingPipeline

def test_extract_deal_data():
    url = "https://www.amazon.in/dp/B0D14FXX8B"
    deal = ScrapingPipeline.process_url(url, source="test")
    print(deal.model_dump_json(indent=2))

if __name__ == "__main__":
    test_extract_deal_data()
