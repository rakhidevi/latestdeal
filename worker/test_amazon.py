import sys
import os
sys.path.append(os.path.abspath(os.path.join(os.path.dirname(__file__), '..')))

from worker.new.providers.amazon.scraper import AmazonScraper
import dataclasses

def main():
    url = "https://www.amazon.in/dp/B0CHX1W1XY"
    if len(sys.argv) > 1:
        url = sys.argv[1]
        
    scraper = AmazonScraper()
    try:
        print(f"Scraping {url}...")
        results = scraper.search(url)
        
        print("Extracting...")
        raw_data = scraper.extract(results[0])
        print("\n--- Raw Data ---")
        print(raw_data)
        
        print("\n--- Normalized DTO ---")
        dto = scraper.normalize(raw_data)
        if dto:
            print(dataclasses.asdict(dto))
            print("Valid:", scraper.validate(dto))
        else:
            print("Normalization failed.")
    except Exception as e:
        print("Error:", e)
    finally:
        scraper.cleanup()

if __name__ == "__main__":
    main()
