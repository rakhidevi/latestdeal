import sys
import os

# Ensure worker root is in path
sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from deal_intelligence import DealIntelligenceEngine
from amazon_scraper import extract_amazon_brand
from bs4 import BeautifulSoup

def test_bajaj_iron_regression():
    """
    CRITICAL REGRESSION TEST:
    Bajaj 7% Iron (₹579 / MRP ₹625) which was erroneously published as a deal.
    With historical price intelligence:
    - 90D low: ₹549
    - 90D median: ₹589
    - 90D high: ₹625
    - 30D low: ₹569
    - 30D high: ₹625
    - 365D low: ₹499
    - 365D high: ₹625
    Current price ₹579 is a routine normal price (~₹10 below median, higher than 30D/90D lows).
    MUST classify as CATALOG_ONLY with verdict WAIT, and deal_score < 40.
    """
    history = {
        "history_30d_low": 569.0,
        "history_30d_high": 625.0,
        "history_90d_low": 549.0,
        "history_90d_high": 625.0,
        "history_90d_median": 589.0,
        "history_365d_low": 499.0,
        "history_365d_high": 625.0
    }

    intel = DealIntelligenceEngine.evaluate(
        current_price=579.0,
        original_price=625.0,
        history=history,
        rating=4.2,
        review_count=15000,
        is_prime=True,
        is_fulfilled=True
    )

    print(f"Bajaj Iron Score: {intel.deal_score}, Qualification: {intel.deal_qualification}, Verdict: {intel.verdict_code}")

    assert intel.deal_score < 40, f"Expected deal_score < 40, got {intel.deal_score}"
    assert intel.deal_qualification == "CATALOG_ONLY", f"Expected CATALOG_ONLY, got {intel.deal_qualification}"
    assert intel.verdict_code == "WAIT", f"Expected WAIT, got {intel.verdict_code}"
    assert intel.historical_status != "ALL_TIME_LOW"
    assert intel.historical_status in ["BELOW_MEDIAN", "NORMAL_PRICE"]

def test_lowest_30d_cannot_bypass_poor_deal_score():
    """
    USER DIRECTIVE:
    Do not let LOWEST_30D/90D automatically override a poor deal score.
    Historical-low status should be a strong signal, not an unconditional bypass.
    If a product hits a 30-day low but the overall deal_score is poor (< 40),
    it MUST remain CATALOG_ONLY and verdict WAIT.
    """
    history = {
        "history_30d_low": 990.0,
        "history_30d_high": 1000.0,
        "history_90d_low": 700.0,
        "history_90d_high": 1000.0,
        "history_90d_median": 850.0,
        "history_365d_low": 600.0,
        "history_365d_high": 1000.0
    }

    # At ₹990, it is technically the lowest in 30 days (ratio_30 = 15 points),
    # but far above 90-day median (₹850) and 90-day low (₹700).
    # MRP is ₹1000 (1% discount).
    intel = DealIntelligenceEngine.evaluate(
        current_price=990.0,
        original_price=1000.0,
        history=history,
        rating=3.0,
        review_count=10,
        is_prime=False
    )

    print(f"Lowest 30D Score: {intel.deal_score}, Status: {intel.historical_status}, Qual: {intel.deal_qualification}")

    assert intel.historical_status == "LOWEST_30D"
    assert intel.deal_score < 40
    # Must NOT bypass to GOOD_DEAL or BUY_NOW
    assert intel.deal_qualification == "CATALOG_ONLY"
    assert intel.verdict_code == "WAIT"

def test_aristocrat_fake_mrp_discount():
    """
    Luggage with an inflated 85% MRP discount (MRP ₹8,660 -> ₹1,299),
    but the typical 90-day price has always been ₹1,299.
    MRP discount must only provide supporting signal (max 10 points),
    so the product does NOT qualify as a HOT_DEAL.
    """
    history = {
        "history_30d_low": 1299.0,
        "history_30d_high": 1299.0,
        "history_90d_low": 1199.0,
        "history_90d_high": 1399.0,
        "history_90d_median": 1299.0,
        "history_365d_low": 999.0,
        "history_365d_high": 1499.0
    }

    intel = DealIntelligenceEngine.evaluate(
        current_price=1299.0,
        original_price=8660.0,
        history=history,
        rating=4.0,
        review_count=500,
        is_prime=True
    )

    print(f"Aristocrat Fake MRP Score: {intel.deal_score}, Qual: {intel.deal_qualification}")

    # Should not qualify as HOT_DEAL because it is at the 90-day median
    assert intel.deal_qualification != "HOT_DEAL"
    assert intel.historical_status in ["NORMAL_PRICE", "LOWEST_30D"]

def test_genuine_hot_deal_all_time_low():
    """
    Legitimate hot deal: All-time low, massive drop from 90D median, excellent rating.
    """
    history = {
        "history_30d_low": 1499.0,
        "history_30d_high": 1999.0,
        "history_90d_low": 1499.0,
        "history_90d_high": 2199.0,
        "history_90d_median": 1899.0,
        "history_365d_low": 1299.0,
        "history_365d_high": 2499.0
    }

    intel = DealIntelligenceEngine.evaluate(
        current_price=999.0,
        original_price=2499.0,
        history=history,
        rating=4.5,
        review_count=2500,
        is_prime=True,
        is_fulfilled=True
    )

    print(f"Genuine Hot Deal Score: {intel.deal_score}, Status: {intel.historical_status}, Qual: {intel.deal_qualification}")

    assert intel.historical_status == "ALL_TIME_LOW"
    assert intel.deal_score >= 80
    assert intel.deal_qualification == "HOT_DEAL"
    assert intel.verdict_code == "BUY_NOW"

class MockPage:
    def __init__(self, html):
        self._html = html
        self._soup = BeautifulSoup(html, 'html.parser')

    def content(self):
        return self._html

    def locator(self, selector):
        return self

    def count(self):
        return len(self._soup.select(self._current_sel if hasattr(self, '_current_sel') else 'body'))

    def first(self):
        return self

    def inner_text(self, timeout=1000):
        el = self._soup.select_one(self._current_sel if hasattr(self, '_current_sel') else 'body')
        return el.get_text() if el else ""

    def query_selector(self, selector):
        self._current_sel = selector
        el = self._soup.select_one(selector)
        if el:
            return MockElement(el)
        return None

class MockElement:
    def __init__(self, el):
        self._el = el

    def inner_text(self):
        return self._el.get_text()

    def text_content(self):
        return self._el.get_text()

    def get_attribute(self, attr):
        return self._el.get(attr)

def test_brand_extraction_bajaj_never_amazon():
    """
    Brand extraction priority:
    JSON-LD -> #bylineInfo / Store -> Product Overview Table -> Title Tokens.
    Must extract 'Bajaj', NEVER 'Amazon'.
    """
    # 1. JSON-LD Test
    json_ld_html = """
    <html>
      <head>
        <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "Product",
          "name": "Bajaj DX-7 Dry Iron",
          "brand": {
            "@type": "Brand",
            "name": "Bajaj"
          }
        }
        </script>
      </head>
      <body>
        <a id="bylineInfo">Brand: Amazon</a>
      </body>
    </html>
    """
    page = MockPage(json_ld_html)
    brand = extract_amazon_brand(page, "Bajaj DX-7 Dry Iron")
    assert brand == "Bajaj", f"Expected 'Bajaj', got '{brand}'"

    # 2. #bylineInfo Store Link Test
    byline_html = """
    <html>
      <body>
        <a id="bylineInfo" class="a-link-normal">Visit the Philips Store</a>
      </body>
    </html>
    """
    page2 = MockPage(byline_html)
    brand2 = extract_amazon_brand(page2, "Philips Daily Collection Mixer Grinder")
    assert brand2 == "Philips", f"Expected 'Philips', got '{brand2}'"

    # 3. Product Overview Table Test
    po_html = """
    <html>
      <body>
        <tr class="po-brand">
          <td class="a-span3"><span class="a-size-base a-text-bold">Brand</span></td>
          <td class="a-span9"><span class="a-size-base po-break-word">Prestige</span></td>
        </tr>
      </body>
    </html>
    """
    page3 = MockPage(po_html)
    brand3 = extract_amazon_brand(page3, "Prestige Induction Cooktop")
    assert brand3 == "Prestige", f"Expected 'Prestige', got '{brand3}'"

    # 4. Title Extraction Fallback Test
    empty_html = "<html><body><div>No brand info</div></body></html>"
    page4 = MockPage(empty_html)
    brand4 = extract_amazon_brand(page4, "Sony WH-1000XM5 Wireless Headphones")
    assert brand4 == "Sony", f"Expected 'Sony', got '{brand4}'"

    # 5. Marketplace Rejection Guard
    # If store says "Visit the Amazon Store", it MUST NOT return "Amazon"
    amazon_store_html = """
    <html>
      <body>
        <a id="bylineInfo">Visit the Amazon Store</a>
      </body>
    </html>
    """
    page5 = MockPage(amazon_store_html)
    brand5 = extract_amazon_brand(page5, "Echo Dot 5th Gen")
    assert brand5 != "Amazon", f"Brand extraction returned 'Amazon'!"

def test_rufus_price_history_integration():
    """
    Verify that the exact dictionary structure emitted by Rufus AI in sitestripe_scraper.py
    properly feeds into DealIntelligenceEngine and determines the historical deal metrics.
    """
    rufus_history = {
        "history_30d_low": 1499.0,
        "history_30d_high": 1999.0,
        "history_90d_low": 1499.0,
        "history_90d_high": 2499.0,
        "history_90d_median": 1999.0,
        "history_365d_low": 1299.0,
        "history_365d_high": 2999.0,
        "source": "rufus_ai"
    }

    # Product priced at ₹1,349 (near all-time low of 1299, below 30d/90d low)
    intel = DealIntelligenceEngine.evaluate(
        current_price=1349.0,
        original_price=3499.0,
        history=rufus_history,
        rating=4.4,
        review_count=1200,
        is_prime=True,
        is_fulfilled=True
    )

    print(f"Rufus Test Score: {intel.deal_score}, Status: {intel.historical_status}, Qual: {intel.deal_qualification}")
    assert intel.deal_score >= 80, f"Expected high score for Rufus drop, got {intel.deal_score}"
    assert intel.deal_qualification in ["HOT_DEAL", "GOOD_DEAL"]
    assert intel.source == "rufus_ai" or intel.historical_status in ["ALL_TIME_LOW", "LOWEST_90D"]

if __name__ == "__main__":
    print("\n--- Running Deal Intelligence Test Suite ---")
    test_bajaj_iron_regression()
    print("[PASS] test_bajaj_iron_regression passed")
    test_lowest_30d_cannot_bypass_poor_deal_score()
    print("[PASS] test_lowest_30d_cannot_bypass_poor_deal_score passed")
    test_aristocrat_fake_mrp_discount()
    print("[PASS] test_aristocrat_fake_mrp_discount passed")
    test_genuine_hot_deal_all_time_low()
    print("[PASS] test_genuine_hot_deal_all_time_low passed")
    test_brand_extraction_bajaj_never_amazon()
    print("[PASS] test_brand_extraction_bajaj_never_amazon passed")
    test_rufus_price_history_integration()
    print("[PASS] test_rufus_price_history_integration passed")
    print("\n*** ALL 6 DEAL INTELLIGENCE & BRAND TESTS PASSED SUCCESSFULLY! ***\n")

