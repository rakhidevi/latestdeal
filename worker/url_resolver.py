import requests
import urllib.parse
from urllib.parse import urlparse
from models import UrlResolveFailed
from playwright.sync_api import sync_playwright

def resolve_url(url: str, timeout: int = 25) -> str:
    """
    Expands shortlinks (like amzn.to, bit.ly, indfs.in) and follows redirects 
    to get the final canonical URL. Uses Playwright to traverse aggregator pages.
    """
    try:
        # Step 1: Try fast HTTP HEAD/GET resolution for direct shortlinks
        headers = {
            "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36",
            "Accept-Language": "en-US,en;q=0.9",
        }
        res = requests.head(url, allow_redirects=True, headers=headers, timeout=10)
        if res.status_code >= 400:
            res = requests.get(url, allow_redirects=True, headers=headers, timeout=10, stream=True)
            
        final_url = res.url
        
        # Step 2: If the URL is still an aggregator (like indiafreestuff), use Playwright to find the merchant link
        if "indiafreestuff.in" in final_url or "indfs.in" in final_url:
            from browser_utils import setup_browser_stateless, get_page
            with sync_playwright() as p:
                browser, context = setup_browser_stateless(p)
                try:
                    page = get_page(context)
                    page.goto(final_url, wait_until="domcontentloaded", timeout=timeout*1000)
                    
                    # Find the 'Shop Now' button or merchant outbound link
                    shop_now_link = None
                    for a in page.locator("a").all():
                        text = a.inner_text().strip().lower()
                        href = a.get_attribute("href") or ""
                        if "shop now" in text:
                            shop_now_link = href
                            break
                            
                    if shop_now_link:
                        # Navigate one more time to resolve the affiliate jump link (like linkredirect.in)
                        page.goto(shop_now_link, wait_until="domcontentloaded", timeout=timeout*1000)
                        final_url = page.url
                finally:
                    context.close()
                    browser.close()

        # Step 3: Clean query parameters for Amazon
        parsed = urlparse(final_url)
        if "amazon" in parsed.netloc:
            query = urllib.parse.parse_qs(parsed.query)
            clean_query = {}
            if 'th' in query: clean_query['th'] = query['th']
            if 'smid' in query: clean_query['smid'] = query['smid']
            encoded = urllib.parse.urlencode(clean_query, doseq=True)
            final_url = f"{parsed.scheme}://{parsed.netloc}{parsed.path}"
            if encoded:
                final_url += f"?{encoded}"
                
        return final_url

    except Exception as e:
        raise UrlResolveFailed(f"Failed to resolve URL {url}: {str(e)}")
