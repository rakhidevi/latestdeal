
from html.parser import HTMLParser
import urllib.request

class MyHTMLParser(HTMLParser):
    def handle_starttag(self, tag, attrs):
        if tag == 'img':
            for attr in attrs:
                if attr[0] == 'src':
                    print('IMG SRC:', attr[1])

parser = MyHTMLParser()
try:
    with urllib.request.urlopen('https://latestdeal.in') as response:
        html = response.read().decode('utf-8')
        parser.feed(html)
except Exception as e:
    print(e)

