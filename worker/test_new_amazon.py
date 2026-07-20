import sys
from worker.new.providers.amazon.scraper import AmazonScraper
from worker.new.core.errors import ParsingError, ValidationError

def run_test():
    url = "https://www.amazon.in/dp/B0CHX1W1XY"
    print(f"Testing new Amazon architecture against: {url}")
    
    scraper = AmazonScraper()
    scraper.initialize()
    
    try:
        results = scraper.search(url)
        print("Search complete.")
        html = results[0]
        
        with open("amazon_test.html", "w", encoding="utf-8") as f:
            f.write(html)
        
        print("Extracting...")
        raw = scraper.extract(html)
        print("Raw Data Extracted:", raw)
        
        print("Normalizing...")
        dto = scraper.normalize(raw)
        print("DTO Normalized:", dto)
        
        print("Validating...")
        scraper.validate(dto)
        print("Validation Passed!")
        print("Final JSON output structure:", {
            "title": dto.product.title,
            "current_price": dto.price.current,
            "image": dto.product.image_url
        })
    except Exception as e:
        print("Test failed with exception:", e)
    finally:
        scraper.cleanup()

if __name__ == "__main__":
    run_test()
