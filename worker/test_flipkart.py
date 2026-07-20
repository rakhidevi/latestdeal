from worker.new.providers.flipkart.scraper import FlipkartScraper

url = "https://www.flipkart.com/apple-iphone-15-black-128-gb/p/itm6ac6485515ae4"

scraper = FlipkartScraper()
try:
    print(f"Scraping {url}...")
    results = scraper.search(url)
    print("Done. Saved snapshot.")
    
    with open("flipkart_dump.html", "w", encoding="utf-8") as f:
        f.write(results[0])
except Exception as e:
    print("Error:", e)
finally:
    scraper.cleanup()
