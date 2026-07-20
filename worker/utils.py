import urllib.parse
import re
import requests
from domains import is_amazon_url

def extract_amazon_asin(url: str) -> str:
    """Extracts ASIN from a given Amazon URL."""
    # Look for /dp/ASIN or /product/ASIN or /ASIN in the URL
    match = re.search(r'/(?:dp|product|exec/obidos/ASIN)/([A-Z0-9]{10})(?:[/?]|$)', url)
    if not match:
        # Check if the URL ends with an ASIN
        match = re.search(r'/([A-Z0-9]{10})(?:[/?]|$)', url)
    return match.group(1) if match else None

def clean_amazon_url(url: str, resolve_redirects: bool = True) -> str:
    """
    Cleans an Amazon URL by stripping all tracking/affiliate params.
    If resolve_redirects is True, it will first resolve shortlinks (like amzn.to or indiafreestuff).
    """
    final_url = url
    if resolve_redirects:
        try:
            # Resolve redirects to get the actual Amazon URL (e.g. from indiafreestuff.in)
            headers = {"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)"}
            res = requests.head(url, allow_redirects=True, headers=headers, timeout=10)
            if res.status_code >= 400:
                res = requests.get(url, allow_redirects=True, headers=headers, timeout=10)
            final_url = res.url
        except Exception:
            pass

    if not is_amazon_url(final_url):
        # If it's not an Amazon link after resolving, we can't clean it as an Amazon URL.
        return final_url

    asin = extract_amazon_asin(final_url)
    if not asin:
        return final_url

    # Reconstruct the URL with ONLY the ASIN and strictly allowed parameters (e.g., th for variations)
    parsed = urllib.parse.urlparse(final_url)
    query = urllib.parse.parse_qs(parsed.query)
    
    clean_query = {}
    if 'th' in query:
        clean_query['th'] = query['th']
    if 'smid' in query:
        clean_query['smid'] = query['smid']
        
    encoded_query = urllib.parse.urlencode(clean_query, doseq=True)
    clean_url = f"https://www.amazon.in/dp/{asin}"
    if encoded_query:
        clean_url += f"?{encoded_query}"
        
    return clean_url
