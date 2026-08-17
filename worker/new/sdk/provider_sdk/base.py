from abc import ABC, abstractmethod
from typing import Dict, Any, List, Optional
from worker.new.sdk.foundation.dto.models import (
    PluginManifestV2, SearchTargetDTO, UniversalProductDTO,
    CanonicalDealDTO, PublishingLedgerV2DTO, TraceContext
)

class CapabilityDescriptor(ABC):
    """Describes what a provider can and cannot do."""
    def __init__(self, manifest: PluginManifestV2):
        self.manifest = manifest
        
    def supports_filter(self, filter_name: str) -> bool:
        return hasattr(self.manifest, f"supports_{filter_name}") and getattr(self.manifest, f"supports_{filter_name}")

class ProviderHealth(ABC):
    """Tracks the health of the provider integration (e.g. captcha rates)."""
    @abstractmethod
    def record_success(self) -> None:
        pass
        
    @abstractmethod
    def record_failure(self, reason: str) -> None:
        pass
        
    @abstractmethod
    def get_health_score(self) -> float:
        pass

class BaseDiscoveryProvider(ABC):
    """Contract for discovering deals on a specific provider."""
    @abstractmethod
    def generate_urls(self, target: SearchTargetDTO) -> List[str]:
        """Translates abstract target constraints into concrete provider URLs."""
        pass

class BaseExtractor(ABC):
    """Contract for extracting universal products and deals from provider HTML/API."""
    @abstractmethod
    def extract_product(self, raw_payload: Dict[str, Any], trace_context: TraceContext) -> Optional[UniversalProductDTO]:
        pass
        
    @abstractmethod
    def extract_deal(self, raw_payload: Dict[str, Any], product_uuid: str, trace_context: TraceContext) -> Optional[CanonicalDealDTO]:
        pass
        
    @abstractmethod
    def extract_grid(self, raw_payload: Dict[str, Any], target: SearchTargetDTO) -> List[UniversalProductDTO]:
        """Parses a discovery grid (search/deals/coupons) into product representations."""
        pass

class BaseValidator(ABC):
    """Contract for provider-specific validation rules (e.g. FBA seller check)."""
    @abstractmethod
    def validate(self, product: UniversalProductDTO, deal: CanonicalDealDTO) -> bool:
        pass
        
    @abstractmethod
    def get_rejection_reason(self) -> Optional[str]:
        pass

class BasePublisher(ABC):
    """Contract for translating opportunities into provider-specific affiliate links."""
    @abstractmethod
    def generate_affiliate_payload(self, deal: CanonicalDealDTO) -> Dict[str, Any]:
        pass

class BaseProvider(ABC):
    """The master Plugin SDK contract that every provider must implement."""
    
    @abstractmethod
    def get_manifest(self) -> PluginManifestV2:
        pass
        
    @abstractmethod
    def get_discovery_provider(self) -> BaseDiscoveryProvider:
        pass
        
    @abstractmethod
    def get_extractor(self) -> BaseExtractor:
        pass
        
    @abstractmethod
    def get_validator(self) -> BaseValidator:
        pass
        
    @abstractmethod
    def get_publisher(self) -> BasePublisher:
        pass
        
    @abstractmethod
    def get_health_tracker(self) -> ProviderHealth:
        pass
