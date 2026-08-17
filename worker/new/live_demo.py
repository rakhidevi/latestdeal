"""
Live Deal Hunter - Multi-Category MRP Error Discovery
Scans Amazon India across all categories, picks the HIGHEST genuine discount,
verifies it mathematically from the DOM, then generates an affiliate link.
"""
import os
import sys

# Add project root to path
project_root = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..'))
sys.path.append(project_root)

import json
import requests
from playwright.sync_api import sync_playwright
from playwright_stealth import Stealth
from worker.new.providers.amazon.provider import AmazonPublisher
from worker.new.sdk.foundation.dto.models import CanonicalDealDTO

# Load the full discovery matrix from the knowledge base
_KNOWLEDGE_DIR = os.path.join(project_root, "worker", "new", "knowledge", "amazon", "compiled")
_CATEGORIES_FILE = os.path.join(_KNOWLEDGE_DIR, "categories_v3.json")

def _load_discovery_matrix():
    """
    Load the full category x brand discovery matrix from categories_v3.json.
    Returns:
      - CATEGORY_NODES: list of (name, rh_filter) for category-level scans
      - BRAND_CATEGORY_NODES: list of (name, rh_filter) for brand x category scans
      - CATEGORY_MAX_MRP: dict[cat_name] -> max believable MRP
      - CATEGORY_MIN_PRICE: dict[cat_name] -> min deal price
    """
    with open(_CATEGORIES_FILE, "r", encoding="utf-8") as f:
        data = json.load(f)

    category_nodes = []
    brand_category_nodes = []
    max_mrp_map = {}
    min_price_map = {}

    for cat in data["categories"]:
        node = cat["amazon_node"]
        name = cat["name"]
        max_mrp_map[name] = cat.get("max_mrp", 50000)
        min_price_map[name] = cat.get("min_price", 200)

        # Category-level scan (no brand filter)
        category_nodes.append((name, f"n:{node}"))

        # Brand x Category scans
        for brand in cat.get("brands", []):
            label = f"{name} > {brand}"
            brand_category_nodes.append((label, f"n:{node},p_89:{brand}"))

    return category_nodes, brand_category_nodes, max_mrp_map, min_price_map

CATEGORY_NODES, BRAND_CATEGORY_NODES, CATEGORY_MAX_MRP, CATEGORY_MIN_PRICE = _load_discovery_matrix()

# Combined scan list: category-level first (fast hits), then brand-level (deep search)
ALL_SCAN_TARGETS = CATEGORY_NODES + BRAND_CATEGORY_NODES

MIN_REVIEW_COUNT = 50    # Must have market presence

# Build a flat set of all curated brand names from categories_v3.json (lowercase).
# Used as a TRUST BONUS only — NOT a block. Unknown brands can still pass.
_with_data = json.load(open(_CATEGORIES_FILE, encoding='utf-8'))
TRUSTED_BRANDS = {
    b.lower().strip()
    for cat in _with_data['categories']
    for b in cat.get('brands', [])
}

# Per-category price-to-MRP ratio cap.
# If real_price is low AND ratio is extreme (>10x), the MRP is likely inflated.
# These caps are HEURISTICS per product tier, not absolute truth.
CATEGORY_MAX_RATIO = {
    'Refrigerator': 5, 'Air Conditioner': 5, 'Washing Machine': 5,
    'Television': 8, 'Smartphone': 8, 'Laptop': 8, 'Tablet': 8,
    'Desktop & Computer': 8, 'Camera': 10, 'Headphones & Audio': 10,
    'Smartwatch & Wearables': 10, 'Gaming': 10, 'Networking & Routers': 8,
    'Storage & Memory': 8, 'Printer': 8, 'Microwave Oven': 6,
    'Kitchen Appliances': 8, 'Personal Care': 10, 'Power Tools': 8,
    "Men's Fashion": 8, "Women's Fashion": 8, 'Footwear': 10,
    'Luggage & Bags': 8, 'Beauty & Skincare': 10, 'Sports & Fitness': 10,
    'Home Decor & Furniture': 8, 'Books & Stationery': 5,
    'Mobile Accessories': 20,
}

# Trust score thresholds
TRUST_VERIFIED  = 50   # High confidence — definitely post
TRUST_FLAGGED   = 25   # Moderate confidence — post with caution label
# Below 25 → reject


def get_js_extract_deals(min_discount):
    return """() => {
    const results = [];
    const elements = document.querySelectorAll('[data-component-type="s-search-result"]');
    for (let el of elements) {
        const asin = el.getAttribute('data-asin');
        if (!asin || asin.length !== 10) continue;

        let titleEl = el.querySelector('h2 span') || el.querySelector('h2');
        let title = titleEl ? titleEl.innerText.trim() : 'Unknown';

        // Current Price
        let priceEl = el.querySelector('.a-price-whole');
        if (!priceEl) continue;
        let price = parseFloat(priceEl.innerText.replace(/,/g, ''));
        if (!price || price <= 0) continue;

        // MRP: Must contain Rs symbol and must NOT come from a unit-price element.
        // Unit-price elements (e.g. \u20b955,900/100g) have the slash INSIDE the parent
        // container's text even if not in the aria-hidden span itself.
        let mrp = 0;
        let mrpEls = el.querySelectorAll('.a-text-price');
        for (let container of mrpEls) {
            let fullText = container.innerText || '';
            // Skip if container text contains a slash (unit price like /100g, /piece)
            if (fullText.includes('/')) continue;
            let span = container.querySelector('span[aria-hidden="true"]');
            if (!span) continue;
            let txt = span.innerText;
            if (!txt.includes('\u20b9')) continue;
            let val = parseFloat(txt.replace(/[^0-9.]/g, ''));
            // Safety: reject if ratio > 20x (unit-price trap pattern)
            if (val > 0 && val < 100000 && val > price && (val / price) <= 20) {
                mrp = val;
                break;
            }
        }

        if (mrp <= 0) continue;

        let discount = Math.round(((mrp - price) / mrp) * 100.0);
        if (discount >= """ + str(min_discount) + """) {
            results.push({ asin, title: title.replace(/\u200b/g, '').trim().slice(0, 80), price, mrp, discount });
        }
    }
    results.sort((a, b) => b.discount - a.discount);
    return results;
}"""


JS_VERIFY_ON_PRODUCT_PAGE = r"""() => {
    let price = 0, mrp = 0;
    
    // --- Current Price ---
    let priceWhole = document.querySelector('.a-price .a-price-whole');
    let priceFraction = document.querySelector('.a-price .a-price-fraction');
    if (priceWhole) {
        price = parseFloat((priceWhole.innerText + '.' + (priceFraction ? priceFraction.innerText : '00')).replace(/,/g, ''));
    }
    
    // --- MRP extraction: 3-method priority ---
    // Method 1 (BEST): Find the element explicitly labeled "M.R.P." on the page.
    let mrpFromLabel = 0;
    let allEls = document.querySelectorAll('*');
    for (let el of allEls) {
        if (el.children.length > 0) continue;
        let t = el.innerText || '';
        if (/M\.?R\.?P\.?/i.test(t)) {
            let parent = el.parentElement;
            if (parent) {
                let priceEl = parent.querySelector('.a-offscreen') ||
                              parent.querySelector('[aria-hidden="true"]') ||
                              parent.nextElementSibling;
                if (priceEl) {
                    let val = parseFloat((priceEl.innerText || '').replace(/[^0-9.]/g, ''));
                    if (val > 0) { mrpFromLabel = val; break; }
                }
            }
        }
    }
    if (mrpFromLabel > price) mrp = mrpFromLabel;
    
    // Method 2: basisPrice container
    if (!mrp) {
        let basisEl = document.querySelector('.basisPrice .a-offscreen') ||
                      document.querySelector('.basisPrice span[aria-hidden="true"]');
        if (basisEl) {
            let val = parseFloat(basisEl.innerText.replace(/[^0-9.]/g, ''));
            if (val > price) mrp = val;
        }
    }
    
    // Method 3: Strikethrough fallback with full-container slash guard
    if (!mrp) {
        let containers = document.querySelectorAll('.a-text-price');
        for (let c of containers) {
            if ((c.innerText || '').includes('/')) continue;
            let span = c.querySelector('span[aria-hidden="true"]');
            if (!span) continue;
            let val = parseFloat(span.innerText.replace(/[^0-9.]/g, ''));
            if (val > price && val / price <= 20) { mrp = val; break; }
        }
    }

    // --- Typical price (Amazon's own fair-value indicator) ---
    let typicalPrice = 0;
    let typEl = document.querySelector('#typicalPrice .a-offscreen') ||
                document.querySelector('[data-csa-c-content-id="typicalPrice"] .a-offscreen');
    if (typEl) {
        typicalPrice = parseFloat(typEl.innerText.replace(/[^0-9.]/g, '')) || 0;
    }

    // --- Amazon deal badge (ground truth that Amazon validated this deal) ---
    let hasDealBadge = false;
    let badgeEls = document.querySelectorAll('.dealBadge, #dealBadge, .a-badge-label, [id*="deal"], [class*="deal"]');
    for (let b of badgeEls) {
        let t = (b.innerText || '').toLowerCase();
        if (t.includes('deal') || t.includes('lightning') || t.includes('savings') || t.includes('offer')) {
            hasDealBadge = true; break;
        }
    }

    // --- Title ---
    let titleEl = document.querySelector('#productTitle');
    let title = titleEl ? titleEl.innerText.trim() : '';
    
    // --- Brand ---
    let brandEl = document.querySelector('#bylineInfo') || document.querySelector('#brand');
    let brand = '';
    if (brandEl) {
        brand = brandEl.innerText.trim();
        brand = brand.replace(/^Brand:\s*/i, '').replace(/^Visit the\s+/i, '').replace(/\s+Store$/i, '').trim();
    }
    
    // --- Review count ---
    let reviewEl = document.querySelector('#acrCustomerReviewText');
    let reviewCount = parseInt((reviewEl ? reviewEl.innerText : '0').replace(/[^0-9]/g, '')) || 0;
    
    // --- Image URL ---
    let imgEl = document.querySelector('#landingImage') || document.querySelector('#imgTagWrapperId img');
    let imageUrl = imgEl ? (imgEl.getAttribute('data-old-hires') || imgEl.src) : '';
    
    if (price > 0 && mrp > 0) {
        let discount = Math.round(((mrp - price) / mrp) * 100.0);
        return { price, mrp, discount, title, brand, reviewCount, hasDealBadge, typicalPrice, imageUrl };
    }
    return null;
}"""


def hunt_and_verify(page, min_discount):
    """
    Full scan across all 356 category x brand targets.
    - Deduplicates by ASIN so same product is never verified twice
    - Collects ALL verified genuine deals (not just the first)
    - Returns list sorted by discount descending
    """
    verified_deals = []
    seen_asins = set()          # Dedup: skip if already verified this ASIN
    rejected_asins = set()      # Dedup: skip if already rejected this ASIN

    def safe_goto(target_url, timeout=30000):
        """Navigate safely — catches net::ERR_FAILED and other transient errors."""
        try:
            page.goto(target_url, wait_until="domcontentloaded", timeout=timeout)
            return True
        except Exception as e:
            print(f"   [NAV ERROR] {str(e)[:80]} — skipping")
            return False

    def go_back_to_search(target_url):
        """Return to the search listing after visiting a product page."""
        if safe_goto(target_url):
            try:
                page.wait_for_selector('[data-component-type="s-search-result"]', timeout=5000)
            except Exception:
                pass

    print(f"\n[MATRIX] {len(ALL_SCAN_TARGETS)} targets ({len(CATEGORY_NODES)} categories + {len(BRAND_CATEGORY_NODES)} brand-specific)")

    for i, (cat_name, node_filter) in enumerate(ALL_SCAN_TARGETS):
        url = f"https://www.amazon.in/s?rh={node_filter}&s=discount-desc-rank"
        print(f"\n[{i+1}/{len(ALL_SCAN_TARGETS)}] {cat_name}")

        if not safe_goto(url):
            continue

        try:
            page.wait_for_selector('[data-component-type="s-search-result"]', timeout=7000)
        except Exception:
            print(f"   -> No results")
            continue

        js_script = get_js_extract_deals(min_discount)
        deals = page.evaluate(js_script)
        if not deals:
            print(f"   -> No {min_discount}%+ candidates")
            continue

        for d in deals:
            asin = d['asin']
            d['category'] = cat_name.split(' > ')[0].strip()
            d['brand_label'] = cat_name

            # Skip ASINs we have already processed
            if asin in seen_asins:
                print(f"   SKIP: {asin} already confirmed LOOT")
                continue
            if asin in rejected_asins:
                print(f"   SKIP: {asin} already rejected")
                continue

            print(f"   CANDIDATE: {d['discount']}% | Rs.{d['price']} / MRP Rs.{d['mrp']} | {d['title'][:50]}")

            verified = verify_candidate(page, d, min_discount)
            if verified:
                verified_deals.append(verified)
                seen_asins.add(asin)
                print(f"   => LOOT #{len(verified_deals)} confirmed!")
            else:
                rejected_asins.add(asin)

            # Always navigate back to the search listing to process next candidate
            go_back_to_search(url)

    return sorted(verified_deals, key=lambda x: x.get('verified_discount', 0), reverse=True)



def compute_trust_score(real, cat, candidate):
    """
    Score a deal from 0-100 based on multiple signals.
    Does NOT block any brand — scores are additive.
    Returns (score, reasons_list).
    """
    score = 0
    reasons = []
    price   = real.get('price', 0)
    mrp     = real.get('mrp', 0)
    disc    = real.get('discount', 0)
    brand   = real.get('brand', '').strip().lower()
    reviews = real.get('reviewCount', 0)
    has_badge    = real.get('hasDealBadge', False)
    typical_price = real.get('typicalPrice', 0)
    ratio   = mrp / price if price > 0 else 999
    max_ratio = CATEGORY_MAX_RATIO.get(cat, 10)

    # ── POSITIVE SIGNALS ────────────────────────────────────────
    # Amazon itself flagged this as a deal (most trustworthy signal)
    if has_badge:
        score += 35
        reasons.append('Amazon deal badge (+35)')

    # Brand is in our curated trusted list (BONUS only — not a block)
    if brand and brand in TRUSTED_BRANDS:
        score += 25
        reasons.append(f'trusted brand {brand} (+25)')
    elif brand and brand not in {'generic', 'unbranded', 'unknown', ''}:
        score += 10
        reasons.append(f'known brand {brand} (+10)')

    # Reviews indicate real product with real buyers
    if reviews >= 2000:
        score += 20
        reasons.append(f'{reviews} reviews (+20)')
    elif reviews >= 500:
        score += 15
        reasons.append(f'{reviews} reviews (+15)')
    elif reviews >= 100:
        score += 8
        reasons.append(f'{reviews} reviews (+8)')

    # Amazon "Typical price" confirms the MRP is fair
    if typical_price > 0 and mrp <= typical_price * 1.15:
        score += 20
        reasons.append(f'typical price Rs.{typical_price} confirms MRP (+20)')

    # ── NEGATIVE SIGNALS ────────────────────────────────────────
    # Ratio far exceeds category norms → likely inflated MRP
    if ratio > max_ratio * 2:
        score -= 30
        reasons.append(f'ratio {ratio:.1f}x >> category max {max_ratio}x (-30)')
    elif ratio > max_ratio:
        score -= 15
        reasons.append(f'ratio {ratio:.1f}x > category max {max_ratio}x (-15)')

    # Very few reviews + extreme discount → fresh fake MRP listing
    if reviews < 100 and disc > 90:
        score -= 20
        reasons.append(f'few reviews + extreme discount (-20)')

    # Unbranded / generic — highest risk
    if brand in {'generic', 'unbranded', 'unknown', ''}:
        score -= 25
        reasons.append('unbranded product (-25)')

    return score, reasons


def verify_candidate(page, candidate, min_discount):
    """Visit product page, compute trust score, return enriched deal or None."""
    asin = candidate['asin']
    cat  = candidate['category']
    max_mrp   = CATEGORY_MAX_MRP.get(cat, 50000)
    min_price = CATEGORY_MIN_PRICE.get(cat, 200)

    try:
        page.goto(f"https://www.amazon.in/dp/{asin}", wait_until="domcontentloaded", timeout=20000)
        page.wait_for_selector('#productTitle', timeout=8000)
        real = page.evaluate(JS_VERIFY_ON_PRODUCT_PAGE)
    except Exception:
        return None

    if not real:
        print(f"   REJECTED: Could not read product page")
        return None

    real_disc  = real.get('discount', 0)
    real_mrp   = real.get('mrp', 0)
    real_price = real.get('price', 0)
    brand      = real.get('brand', '').strip().lower()
    reviews    = real.get('reviewCount', 0)

    # Hard gates (non-negotiable)
    if real_disc < min_discount:
        print(f"   REJECTED: {real_disc}% — below threshold (unit price trap?)")
        return None
    if real_mrp > max_mrp:
        print(f"   REJECTED: MRP Rs.{real_mrp} > category max Rs.{max_mrp}")
        return None
    if real_price < min_price:
        print(f"   REJECTED: Price Rs.{real_price} too low to post")
        return None
    if reviews < MIN_REVIEW_COUNT:
        print(f"   REJECTED: Only {reviews} reviews")
        return None

    # Trust score
    trust, reasons = compute_trust_score(real, cat, candidate)
    trust_label = 'VERIFIED' if trust >= TRUST_VERIFIED else ('FLAGGED' if trust >= TRUST_FLAGGED else 'REJECT')

    print(f"   TRUST: {trust}/100 [{trust_label}] | {real_disc}% OFF | Rs.{real_price}/MRP Rs.{real_mrp} | {brand} | {reviews} reviews")
    for r in reasons:
        print(f"          {r}")

    if trust < TRUST_FLAGGED:
        print(f"   REJECTED: Trust score {trust} too low (likely fake MRP)")
        return None

    candidate.update({
        'verified_discount': real_disc,
        'verified_price': real_price,
        'verified_mrp': real_mrp,
        'title': real.get('title') or candidate['title'],
        'trust_score': trust,
        'trust_label': trust_label,
        'trust_reasons': reasons,
    })
    return candidate


def show_publisher_live(min_discount=60, deal_type="deal"):
    print(f"\n{'='*60}")
    print(f"  AMAZON LOOT HUNTER - {deal_type.upper()} MODE")
    print(f"  {len(CATEGORY_NODES)} categories x {len(BRAND_CATEGORY_NODES)} brand combos")
    print(f"  Threshold: {min_discount}%+ DOM-verified discount")
    print(f"{'='*60}")

    # ── PHASE 1: SCAN ────────────────────────────────────────────
    # Use headless Playwright Chromium (no Chrome profile needed for reading Amazon).
    # This avoids the SingletonLock crash entirely — no profile = no lock file.
    print("\n[PHASE 1] Scanning with headless browser (no profile lock)...")
    with sync_playwright() as p:
        scan_browser = p.chromium.launch(
            headless=True,
            args=["--disable-blink-features=AutomationControlled", "--no-sandbox"]
        )
        scan_page = scan_browser.new_page()
        Stealth().use_sync(scan_page)
        # Set a realistic user agent
        scan_page.set_extra_http_headers({
            "Accept-Language": "en-IN,en;q=0.9",
            "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8"
        })

        verified_deals = hunt_and_verify(scan_page, min_discount)
        scan_browser.close()

    if not verified_deals:
        print(f"\n[RESULT] No genuine {min_discount}%+ deals found across all targets right now.")
        return

    print(f"\n{'='*60}")
    print(f"  SCAN COMPLETE — {len(verified_deals)} GENUINE LOOT DEAL(S) FOUND!")
    print(f"{'='*60}")

    # ── PHASE 2: AFFILIATE LINKS ─────────────────────────────────
    # Open the real Chrome profile ONCE for SiteStripe link generation.
    print("\n[PHASE 2] Generating affiliate links via SiteStripe...")
    profile_dir = os.path.join(project_root, "worker", "browser_profile")
    os.makedirs(profile_dir, exist_ok=True)

    # Clear stale lock files before opening profile
    for lock_name in ["SingletonLock", "SingletonSocket", "SingletonCookie", "lockfile"]:
        lock_path = os.path.join(profile_dir, lock_name)
        try:
            if os.path.exists(lock_path):
                os.remove(lock_path)
        except Exception:
            pass

    # We no longer need local AmazonPublisher for sitestripe, 
    # since we are pushing to Laravel backend which handles dispatching.
    # pub = AmazonPublisher()

    for idx, deal in enumerate(verified_deals, 1):
        trust_label = deal.get('trust_label', '?')
        trust_score = deal.get('trust_score', 0)
        print(f"\n[LOOT #{idx}] [{trust_label} {trust_score}/100]")
        print(f"  Category : {deal['category']}")
        print(f"  Product  : {deal['title'][:70]}")
        print(f"  ASIN     : {deal['asin']}")
        print(f"  Price    : Rs.{deal['verified_price']} (MRP: Rs.{deal['verified_mrp']})")
        print(f"  Discount : {deal['verified_discount']}% OFF")
        if trust_label == 'FLAGGED':
            print(f"  [!] CAUTION: Moderate confidence -- verify before posting")

        # 3. Create DiscoveryJob DTO and push to local queue
        from worker.new.sdk.foundation.dto.models import DiscoveryJob
        from worker.database import enqueue_discovery_job
        from worker.new.sdk.foundation.identity.generator import generate_uuid

        job = DiscoveryJob(
            job_uuid=generate_uuid(),
            trace_id=generate_uuid(),
            provider="amazon",
            provider_product_id=deal['asin'],
            url=f"https://www.amazon.in/dp/{deal['asin']}",
            deal_type=deal_type,
            strategy="search",
            discovery_profile=deal['category'],
            opportunity_score=trust_score,
            evidence_summary={
                'title': deal['title'],
                'mrp': deal['verified_mrp'],
                'price': deal['verified_price'],
                'discount': deal['verified_discount'],
                'trust_metrics': trust_label,
                'image_url': deal.get('image_url', '')
            }
        )
        
        success = enqueue_discovery_job(job.model_dump(mode='json'))
        if success:
            print(f"  [+] QUEUED FOR PUBLISHING -> JOB ID: {job.job_uuid}")
        else:
            print(f"  [-] QUEUE REJECTED (Duplicate?)")

    verified_count = sum(1 for d in verified_deals if d.get('trust_label') == 'VERIFIED')
    flagged_count  = sum(1 for d in verified_deals if d.get('trust_label') == 'FLAGGED')
    print(f"\n{'='*60}")
    print(f"  Total: {len(verified_deals)} deals ({verified_count} VERIFIED, {flagged_count} FLAGGED)")
    print(f"{'='*60}")


if __name__ == "__main__":
    if len(sys.argv) > 1 and sys.argv[1] == "--mega":
        show_publisher_live(min_discount=85, deal_type="mega_loot")
    else:
        show_publisher_live(min_discount=60, deal_type="deal")
