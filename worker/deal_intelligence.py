import datetime
from typing import Optional, Dict, Any
from models import PriceIntelligence, Deal

class DealIntelligenceEngine:
    """
    Deterministic Price Intelligence & Deal Qualification Engine.
    Evaluates deals purely using mathematical signals and historical benchmarks.
    Zero AI dependency.
    """

    @staticmethod
    def evaluate(
        current_price: float,
        original_price: Optional[float] = None,
        history: Optional[Dict[str, Any]] = None,
        rating: Optional[float] = None,
        review_count: Optional[int] = None,
        is_prime: bool = False,
        is_fulfilled: bool = False
    ) -> PriceIntelligence:
        
        mrp = original_price if (original_price and original_price > current_price) else current_price
        mrp_discount = round(((mrp - current_price) / mrp) * 100, 2) if mrp > 0 else 0.0

        has_history = bool(history and ("90d" in history or "30d" in history or "history_90d_median" in history))

        if not has_history:
            # Baseline when historical provider data is not yet available
            # Conservative scoring: MRP discount is only a small supporting factor
            mrp_score = min(25, int(mrp_discount * 0.4))
            quality_score = 0
            if rating and rating >= 4.0:
                quality_score += 5
            if is_prime or is_fulfilled:
                quality_score += 5

            deal_score = mrp_score + quality_score

            if mrp_discount >= 50 and deal_score >= 30:
                deal_qualification = "WATCH"
                verdict_code = "CONSIDER"
                historical_status = "BELOW_MEDIAN"
            else:
                deal_qualification = "CATALOG_ONLY"
                verdict_code = "WAIT"
                historical_status = "NORMAL_PRICE"

            return PriceIntelligence(
                historical_status=historical_status,
                deal_score=deal_score,
                deal_qualification=deal_qualification,
                verdict_code=verdict_code,
                source="heuristic_baseline",
                source_checked_at=datetime.datetime.utcnow().isoformat()
            )

        # Parse Historical Points
        low_30d = float(history.get("history_30d_low") or history.get("30d", {}).get("lowest") or current_price)
        high_30d = float(history.get("history_30d_high") or history.get("30d", {}).get("highest") or mrp)
        
        low_90d = float(history.get("history_90d_low") or history.get("90d", {}).get("lowest") or low_30d)
        high_90d = float(history.get("history_90d_high") or history.get("90d", {}).get("highest") or high_30d)
        median_90d = float(history.get("history_90d_median") or history.get("90d", {}).get("median") or ((low_90d + high_90d) / 2))
        
        low_365d = float(history.get("history_365d_low") or history.get("365d", {}).get("lowest") or low_90d)
        high_365d = float(history.get("history_365d_high") or history.get("365d", {}).get("highest") or high_90d)

        # Percent Differences
        diff_30d_low = round(((current_price - low_30d) / low_30d) * 100, 2) if low_30d > 0 else 0.0
        diff_90d_low = round(((current_price - low_90d) / low_90d) * 100, 2) if low_90d > 0 else 0.0
        diff_90d_median = round(((current_price - median_90d) / median_90d) * 100, 2) if median_90d > 0 else 0.0

        # Classify Historical Status
        if current_price <= low_365d:
            historical_status = "ALL_TIME_LOW"
        elif current_price <= low_90d:
            historical_status = "LOWEST_90D"
        elif current_price <= low_30d:
            historical_status = "LOWEST_30D"
        elif current_price <= low_90d * 1.03:
            historical_status = "NEAR_90D_LOW"
        elif current_price < median_90d:
            historical_status = "BELOW_MEDIAN"
        elif current_price <= median_90d * 1.05:
            historical_status = "NORMAL_PRICE"
        else:
            historical_status = "ABOVE_NORMAL"

        # ----------------------------------------------------
        # Weighted Deal Scoring Engine (0 - 100)
        # Calibrated to detect true price intelligence drops
        # ----------------------------------------------------
        score = 0

        # Spread significance
        spread_30_pct = ((high_30d - low_30d) / high_30d * 100) if high_30d > 0 else 0
        spread_90_pct = ((high_90d - low_90d) / high_90d * 100) if high_90d > 0 else 0

        # 1. Drop vs 90-Day Median / Typical Price (0 - 25)
        # Real deals are noticeably cheaper than what shoppers typically pay
        median_score = 0
        if diff_90d_median <= -25:
            median_score = 25
        elif diff_90d_median <= -15:
            median_score = 18
        elif diff_90d_median <= -8:
            median_score = 10
        elif diff_90d_median <= -3:
            median_score = 3
        score += median_score

        # 2. Closeness to 90-Day Low (0 - 25)
        low_score = 0
        if diff_90d_low <= 0:
            low_score = 25
        elif diff_90d_low <= 1.5:
            low_score = 15
        elif diff_90d_low <= 4.0:
            low_score = 8
        score += low_score

        # 3. All-Time Low / 365-Day Price Position (0 - 20)
        history_score = 0
        if current_price <= low_365d:
            history_score = 20
        elif current_price <= low_30d and spread_30_pct >= 8.0:
            history_score = 10
        score += history_score

        # 4. MRP Discount Supporting Signal (0 - 15)
        # Kept as supporting signal only, cannot alone make a deal
        mrp_score = 0
        if mrp_discount >= 60:
            mrp_score = 15
        elif mrp_discount >= 45:
            mrp_score = 10
        elif mrp_discount >= 30:
            mrp_score = 6
        elif mrp_discount >= 15:
            mrp_score = 3
        score += mrp_score

        # 5. Product Quality / Seller Signals (0 - 15)
        # Quality only adds full value if there is a real price drop or genuine high discount
        base_drop_score = median_score + low_score + history_score
        quality_score = 0
        if rating and rating >= 4.2:
            quality_score += 7
        elif rating and rating >= 3.8:
            quality_score += 4
        if review_count and review_count >= 100:
            quality_score += 5
        if is_prime or is_fulfilled:
            quality_score += 3

        if base_drop_score >= 15 or mrp_discount >= 35:
            score += quality_score
        else:
            score += min(3, quality_score)

        deal_score = max(0, min(100, score))

        # ----------------------------------------------------
        # Deterministic Publishing Gate
        # (NO bypass for poor score: deal_score >= 50 required for status qualification)
        # ----------------------------------------------------
        if deal_score < 40:
            deal_qualification = "CATALOG_ONLY"
            verdict_code = "WAIT"
        elif historical_status == "ALL_TIME_LOW" and deal_score >= 50:
            deal_qualification = "HOT_DEAL"
            verdict_code = "BUY_NOW"
        elif historical_status in ["LOWEST_90D", "LOWEST_30D"] and deal_score >= 50:
            deal_qualification = "GOOD_DEAL"
            verdict_code = "BUY_NOW"
        elif deal_score >= 60:
            deal_qualification = "GOOD_DEAL"
            verdict_code = "BUY_NOW"
        elif historical_status == "BELOW_MEDIAN":
            deal_qualification = "WATCH"
            verdict_code = "CONSIDER"
        else:
            deal_qualification = "CATALOG_ONLY"
            verdict_code = "WAIT"

        return PriceIntelligence(
            history_30d_low=low_30d,
            history_30d_high=high_30d,
            history_90d_low=low_90d,
            history_90d_high=high_90d,
            history_90d_median=median_90d,
            history_365d_low=low_365d,
            history_365d_high=high_365d,
            current_vs_30d_low_pct=diff_30d_low,
            current_vs_90d_low_pct=diff_90d_low,
            current_vs_90d_median_pct=diff_90d_median,
            historical_status=historical_status,
            deal_score=deal_score,
            deal_qualification=deal_qualification,
            verdict_code=verdict_code,
            source="price_engine",
            source_checked_at=datetime.datetime.utcnow().isoformat()
        )
