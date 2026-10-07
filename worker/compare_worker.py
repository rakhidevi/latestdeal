import asyncio
import os
import logging
import requests
import time
from dotenv import load_dotenv

# ---------------------------------------------------------------------------
# Import local SERP service (Rule 3: local infra over cloud APIs)
# ---------------------------------------------------------------------------
from local_serp_service import search_all_stores, PriceResult

load_dotenv()

logger = logging.getLogger(__name__)
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [COMPARE_WORKER] %(message)s"
)

API_BASE = os.getenv('API_URL', 'http://latestdeal.test/api/v1')
WORKER_SECRET = os.getenv('WORKER_SECRET', '')
POLL_INTERVAL = int(os.getenv('COMPARE_POLL_INTERVAL', '2'))  # seconds


def _get_headers() -> dict:
    """Build request headers with optional worker secret."""
    headers = {'Accept': 'application/json', 'Content-Type': 'application/json'}
    if WORKER_SECRET:
        headers['X-Worker-Secret'] = WORKER_SECRET
    return headers


def process_job(job: dict) -> None:
    """
    Process a single compare job using the local SERP service.

    Strategy:
      1. Call search_all_stores() — DuckDuckGo snippet extraction,
         ~1-3s per job, zero browser tabs.
      2. Compute a rudimentary buy signal score based on store count
         and whether the deal price is at or below competitor prices.
      3. POST results to Laravel /worker/compare-jobs/{id}/complete.
    """
    job_id = job.get('id')
    title = job.get('title', '')
    deal_price = job.get('deal_price')
    merchant = job.get('merchant', '')

    logger.info(f"Processing job {job_id}: '{title[:60]}' (Deal price: ₹{deal_price})")

    t0 = time.time()
    # --- Core: search across Amazon, Flipkart, Croma, Reliance Digital ------
    price_results: list[PriceResult] = search_all_stores(title)
    elapsed = time.time() - t0
    logger.info(f"Job {job_id}: SERP scan completed in {elapsed:.1f}s — "
                f"{len(price_results)} stores found.")

    # --- Serialize DTOs for API payload ------------------------------------
    results_payload = [r.to_dict() for r in price_results]

    # --- Buy signal scoring ------------------------------------------------
    # Simple heuristic: if deal price <= lowest competitor price, it's a buy.
    prices_with_values = [r.price for r in price_results if r.price]
    competitor_min = min(prices_with_values) if prices_with_values else None
    ai_score = None

    if deal_price and competitor_min:
        deal_price_num = float(deal_price)
        if deal_price_num <= competitor_min * 0.90:
            ai_score = 95  # 10%+ cheaper than best competitor
        elif deal_price_num <= competitor_min:
            ai_score = 82  # at or below best competitor price
        elif deal_price_num <= competitor_min * 1.05:
            ai_score = 68  # within 5% of best competitor
        else:
            ai_score = 40  # deal price is above best competitor

    # --- Push to Laravel ---------------------------------------------------
    try:
        payload = {
            'results': results_payload,
            'competitor_min_price': competitor_min,
            'scan_duration_seconds': round(elapsed, 2),
        }
        if ai_score is not None:
            payload['ai_score'] = ai_score

        resp = requests.post(
            f"{API_BASE}/worker/compare-jobs/{job_id}/complete",
            json=payload,
            headers=_get_headers(),
            timeout=15
        )
        if resp.status_code in (200, 201):
            logger.info(f"Job {job_id} completed → Laravel {resp.status_code} ✅")
        else:
            logger.warning(f"Job {job_id} → Laravel returned {resp.status_code}: {resp.text[:200]}")

    except requests.exceptions.RequestException as e:
        logger.error(f"Job {job_id}: Failed to push results to Laravel: {e}")


def main():
    """
    Polling daemon: fetch pending compare jobs from Laravel and process them.
    Uses synchronous HTTP calls (no browser pool needed — local SERP is fast).
    """
    logger.info("=" * 60)
    logger.info("Compare Worker (Local SERP Mode) starting...")
    logger.info(f"  API Base: {API_BASE}")
    logger.info(f"  Poll interval: {POLL_INTERVAL}s")
    logger.info("=" * 60)

    while True:
        try:
            resp = requests.get(
                f"{API_BASE}/worker/compare-jobs/pending",
                headers=_get_headers(),
                timeout=10
            )
            if resp.status_code == 200:
                data = resp.json()
                job = data.get('job')
                if job:
                    process_job(job)
                else:
                    # No pending jobs — idle sleep
                    time.sleep(POLL_INTERVAL)
            else:
                logger.warning(f"Pending jobs endpoint returned {resp.status_code}")
                time.sleep(5)

        except requests.exceptions.ConnectionError:
            logger.warning(f"Cannot connect to {API_BASE}. Retrying in 10s...")
            time.sleep(10)
        except requests.exceptions.RequestException as e:
            logger.error(f"Request error: {e}")
            time.sleep(5)
        except KeyboardInterrupt:
            logger.info("Compare Worker shutting down gracefully.")
            break
        except Exception as e:
            logger.error(f"Unhandled error in main loop: {e}")
            time.sleep(5)


if __name__ == "__main__":
    main()
