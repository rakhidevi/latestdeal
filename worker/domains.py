# Common domain configurations for scrapers

# Amazon variations to detect in URLs
AMAZON_DOMAINS = [
    "amazon",
    "amzn",
    "link.amazon"
]

# Specific prefixes to look for when hunting for Amazon product links inside aggregator sites
AMAZON_PRODUCT_PREFIXES = [
    "amazon.in/dp/",
    "amazon.in/gp/",
    "amzn.to",
    "link.amazon"
]

# Aggregators that redirect to Amazon
AGGREGATOR_DOMAINS = [
    "indiafreestuff"
]

def is_amazon_url(url: str) -> bool:
    """Helper to check if a URL belongs to Amazon"""
    if not url: return False
    url_lower = url.lower()
    return any(domain in url_lower for domain in AMAZON_DOMAINS)

def is_aggregator_url(url: str) -> bool:
    """Helper to check if a URL belongs to a known aggregator"""
    if not url: return False
    url_lower = url.lower()
    return any(domain in url_lower for domain in AGGREGATOR_DOMAINS)

def is_amazon_or_aggregator(url: str) -> bool:
    return is_amazon_url(url) or is_aggregator_url(url)
