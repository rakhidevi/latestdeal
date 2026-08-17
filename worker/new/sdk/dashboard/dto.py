from pydantic import BaseModel

class DiscoveryDashboardDTO(BaseModel):
    profiles_running: int = 0
    search_targets_generated: int = 0
    search_targets_queued: int = 0
    extraction_rate_percent: float = 0.0
    average_opportunity_score: float = 0.0

class PublishingDashboardDTO(BaseModel):
    published_today: int = 0
    updated_today: int = 0
    rejected_today: int = 0
    rollbacks_today: int = 0

class ProviderDashboardDTO(BaseModel):
    provider_name: str
    success_rate_percent: float = 0.0
    captcha_rate_percent: float = 0.0
    average_latency_ms: int = 0
    queue_depth: int = 0
