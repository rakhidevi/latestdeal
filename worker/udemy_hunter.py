import requests
from bs4 import BeautifulSoup
import time
import sqlite3
import os
import sys

# DiscUdemy has pages like /all/1, /all/2
BASE_URL = "https://www.couponami.com/all"

def get_course_links(page_url):
    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36"
    }
    response = requests.get(page_url, headers=headers)
    if response.status_code != 200:
        print(f"Failed to fetch {page_url}")
        return []
        
    soup = BeautifulSoup(response.text, 'html.parser')
    courses = []
    
    for a in soup.select('a.card-header'):
        if 'href' in a.attrs:
            courses.append(a['href'])
            
    return courses

def get_udemy_url(course_url):
    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36"
    }
    
    # 1. Fetch course page to find the 'go' link
    response = requests.get(course_url, headers=headers)
    if response.status_code != 200:
        return None
        
    soup = BeautifulSoup(response.text, 'html.parser')
    go_url = None
    for a in soup.select('a'):
        if a.get('href') and '/go/' in a.get('href'):
            go_url = a['href']
            break
            
    if not go_url:
        return None
        
    # 2. Fetch the 'go' page to extract the actual Udemy URL
    go_response = requests.get(go_url, headers=headers)
    if go_response.status_code != 200:
        return None
        
    go_soup = BeautifulSoup(go_response.text, 'html.parser')
    for a in go_soup.select('.ui.segment a'):
        href = a.get('href', '')
        if 'udemy.com' in href and 'couponCode=' in href:
            return href
            
    return None

import database

def queue_deal(url):
    database.init_db()
    try:
        database.add_to_queue(url)
        print(f"Queued Udemy deal: {url}")
    except Exception as e:
        print(f"Database error: {e}")

def hunt_udemy_deals(pages=1):
    print("Hunting for Udemy 100% Off Coupons...")
    for i in range(1, pages + 1):
        url = f"{BASE_URL}/{i}" if i > 1 else BASE_URL
        print(f"Scraping page {i}: {url}")
        
        course_links = get_course_links(url)
        for link in course_links:
            time.sleep(1) # Be polite
            print(f"Checking {link}...")
            udemy_url = get_udemy_url(link)
            if udemy_url:
                queue_deal(udemy_url)
            else:
                print(f"Could not find valid Udemy coupon link for {link}")

if __name__ == "__main__":
    hunt_udemy_deals(1)
