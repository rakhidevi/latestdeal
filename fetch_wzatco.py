
import urllib.request
import re

url = 'https://latestdeal.in'
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
try:
    with urllib.request.urlopen(req) as response:
        html = response.read().decode('utf-8')
        # Find the src of the image near 'Wzatco'
        matches = re.finditer(r'<img[^>]+src="([^"]+)"[^>]*>.*?Wzatco', html, re.DOTALL | re.IGNORECASE)
        for m in matches:
            print('Found IMG SRC before Wzatco:', m.group(1))
            
        matches2 = re.finditer(r'Wzatco.*?<img[^>]+src="([^"]+)"', html, re.DOTALL | re.IGNORECASE)
        for m in matches2:
            print('Found IMG SRC after Wzatco:', m.group(1))
            break
except Exception as e:
    print('Error:', e)

