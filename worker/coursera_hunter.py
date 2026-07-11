import requests
from bs4 import BeautifulSoup
import database

def queue_deal(url):
    database.init_db()
    try:
        database.add_to_queue(url)
        print(f"Queued Coursera course: {url}")
    except Exception as e:
        print(f"Database error: {e}")

def hunt_coursera_deals():
    print("Hunting for Coursera Free Courses...")
    url = "https://www.coursera.org/courses?query=free"
    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36"
    }
    
    response = requests.get(url, headers=headers)
    if response.status_code != 200:
        print("Failed to fetch Coursera page")
        return
        
    soup = BeautifulSoup(response.text, 'html.parser')
    
    # Coursera uses various classes for their course cards, usually wrapped in 'a' tags with href starting with '/learn/' or '/projects/'
    links_found = 0
    for a in soup.find_all('a', href=True):
        href = a['href']
        if href.startswith('/learn/') or href.startswith('/specializations/') or href.startswith('/professional-certificates/'):
            full_url = f"https://www.coursera.org{href}"
            queue_deal(full_url)
            links_found += 1
            if links_found >= 10:  # Just queue the top 10 for now
                break

if __name__ == "__main__":
    hunt_coursera_deals()
