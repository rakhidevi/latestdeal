"""Unit test for AmazonCouponExtractor"""
import sys
import os

project_root = r"K:\WhatsAppUtility\LatestDeal"
sys.path.insert(0, project_root)

from worker.new.providers.amazon.extractors.coupons import AmazonCouponExtractor
from worker.new.sdk.foundation.dto.models import SearchTargetDTO, TraceContext

def test_coupon_extractor_details():
    extractor = AmazonCouponExtractor()
    
    # 1. Test grid extraction with various coupon types
    sample_html = """
    <html>
    <body>
        <!-- 1. Fixed amount coupon -->
        <div class="s-result-item" data-asin="B0FIXEDCPN">
            <h2 class="a-text-normal">Boat Rockerz 450 Bluetooth Headphones</h2>
            <span class="s-coupon-highlight-color">Save ₹300 with coupon. Terms apply.</span>
        </div>
        <!-- 2. Percentage coupon with Prime eligibility -->
        <div class="coupon-item-container" data-asin="B0PRICECPN">
            <span class="coupon-title">Philips Smart Wi-Fi LED Bulb</span>
            <span class="couponLabelText">Save 15% with coupon. Prime Savings.</span>
        </div>
        <!-- 3. Promo code coupon with min spend -->
        <div class="s-result-item" data-asin="B0PROMOCPN">
            <h2 class="a-text-normal">Noise Pulse 2 Max Smartwatch</h2>
            <span class="coupon-badge">Use promo code NOISE20 at checkout. Min spend ₹1000 required.</span>
        </div>
        <!-- 4. Card without coupon (should be skipped) -->
        <div class="s-result-item" data-asin="B0NOCOUPON">
            <h2 class="a-text-normal">Standard Item Without Any Savings</h2>
        </div>
    </body>
    </html>
    """
    
    trace_ctx = TraceContext(trace_id="test-trace", provider="AmazonProvider", strategy="strat_coupons")
    target = SearchTargetDTO(
        trace_context=trace_ctx,
        provider="AmazonProvider",
        strategy="strat_coupons",
        priority=1,
        url="https://amazon.in/coupons",
        expected_content="COUPON_GRID"
    )
    
    products = extractor.extract_grid({"html": sample_html}, target)
    assert len(products) == 3, f"Expected 3 products with coupons, got {len(products)}"
    
    # Verify fixed coupon product
    p1 = next(p for p in products if p.provider_product_id == "B0FIXEDCPN")
    assert any("coupon:value:300.0" in f for f in p1.features)
    assert any("coupon:clip:True" in f for f in p1.features)
    print("Fixed amount coupon extracted successfully:", p1.features[0:3])
    
    # Verify percentage coupon product
    p2 = next(p for p in products if p.provider_product_id == "B0PRICECPN")
    assert any("coupon:percent:15.0" in f for f in p2.features)
    assert any("coupon:eligibility:Prime Exclusive" in f for f in p2.features)
    print("Percentage coupon with Prime eligibility extracted successfully:", p2.features[0:3])
    
    # Verify promo code coupon product
    p3 = next(p for p in products if p.provider_product_id == "B0PROMOCPN")
    assert any("coupon:code:NOISE20" in f for f in p3.features)
    assert any("coupon:clip:False" in f for f in p3.features)
    assert any("Min spend" in f for f in p3.features)
    print("Promo code coupon extracted successfully:", p3.features[0:3])
    
    # 2. Test extract_deal with effective price calculation
    detail_html = """
    <html>
    <body>
        <span class="priceToPay"><span class="a-price-whole">1,499</span></span>
        <span class="a-text-price"><span class="a-offscreen">₹5,999</span></span>
        <div id="coupon-badge">Save ₹200 with coupon</div>
    </body>
    </html>
    """
    deal = extractor.extract_deal({"html": detail_html}, "B0FIXEDCPN", trace_ctx)
    assert deal is not None
    assert deal.price == 1499.0
    assert deal.effective_price == 1299.0 # 1499 - 200 coupon
    assert deal.mrp == 5999.0
    assert "200.0" in deal.coupon_details
    print("extract_deal calculated effective_price correctly: Rs.", deal.effective_price, "from listed Rs.", deal.price)
    
    print("\nALL COUPON EXTRACTOR TESTS PASSED!")

if __name__ == "__main__":
    test_coupon_extractor_details()
