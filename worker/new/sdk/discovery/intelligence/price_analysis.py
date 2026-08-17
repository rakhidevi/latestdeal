from typing import Optional, Dict, Any
from datetime import datetime, timezone, timedelta
from worker.new.sdk.foundation.dto.models import UniversalProductIdentity
from worker.new.sdk.discovery.intelligence.price_history import UniversalPriceHistoryService, PriceObservation

class PriceAnalysisResult:
    def __init__(self):
        self.current_price: float = 0.0
        self.lowest_30_days: Optional[float] = None
        self.lowest_90_days: Optional[float] = None
        self.average_30_days: Optional[float] = None
        self.volatility_score: float = 0.0 # 0 to 100
        self.days_since_lowest: Optional[int] = None
        self.is_buy_signal: bool = False
        self.drop_percentage: float = 0.0

class PriceAnalysisService:
    def __init__(self, history_service: UniversalPriceHistoryService):
        self.history_service = history_service
        
    def analyze(self, identity: UniversalProductIdentity, current_price: float) -> PriceAnalysisResult:
        result = PriceAnalysisResult()
        result.current_price = current_price
        
        history = self.history_service.get_history(identity)
        if not history:
            return result
            
        now = datetime.now(timezone.utc)
        thirty_days_ago = now - timedelta(days=30)
        ninety_days_ago = now - timedelta(days=90)
        
        # Ensure observed_at has timezone info before comparing
        def get_tz_aware(dt: datetime) -> datetime:
            if dt.tzinfo is None:
                return dt.replace(tzinfo=timezone.utc)
            return dt
            
        prices_30d = [obs for obs in history if get_tz_aware(obs.observed_at) >= thirty_days_ago]
        prices_90d = [obs for obs in history if get_tz_aware(obs.observed_at) >= ninety_days_ago]
        
        if prices_30d:
            lowest_30_obs = min(prices_30d, key=lambda x: x.price)
            result.lowest_30_days = lowest_30_obs.price
            result.average_30_days = sum(obs.price for obs in prices_30d) / len(prices_30d)
            result.days_since_lowest = (now - get_tz_aware(lowest_30_obs.observed_at)).days
            
            # Simple volatility: (max - min) / average
            max_30 = max(prices_30d, key=lambda x: x.price).price
            if result.average_30_days > 0:
                result.volatility_score = ((max_30 - result.lowest_30_days) / result.average_30_days) * 100
                
        if prices_90d:
            result.lowest_90_days = min(prices_90d, key=lambda x: x.price).price
            
        # Buy Signal Logic
        # A buy signal is true if the current price is less than or equal to the lowest 30-day price, 
        # AND there's a meaningful drop from the average.
        if result.lowest_30_days is not None and result.average_30_days is not None:
            if current_price <= result.lowest_30_days and current_price < result.average_30_days * 0.95:
                result.is_buy_signal = True
                result.drop_percentage = ((result.average_30_days - current_price) / result.average_30_days) * 100
                
        return result
