from worker.new.sdk.foundation.dto.models import ProviderCapabilityDTO

def get_capabilities() -> ProviderCapabilityDTO:
    return ProviderCapabilityDTO(
        name="amazon",
        version="1.0.0",
        supported_filters=[
            "brand", "discount", "category", "price_range", "prime"
        ],
        rate_limit_req_per_sec=0.2, # 1 request every 5 seconds normally
        features=["pagination", "captcha_detection"]
    )
