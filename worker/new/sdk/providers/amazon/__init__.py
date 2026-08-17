from typing import Dict, Any
from worker.new.sdk.foundation.contracts.provider import CommerceProvider
from worker.new.sdk.foundation.dto.models import ProviderCapabilityDTO
from worker.new.sdk.providers.amazon.capabilities import get_capabilities
from worker.new.sdk.providers.amazon.query_builder import AmazonQueryBuilder

class AmazonProvider(CommerceProvider):
    
    def __init__(self):
        self.query_builder = AmazonQueryBuilder()
        self.capabilities = get_capabilities()
        self.knowledge: Dict[str, Any] = {} # Mocked

    def get_capabilities(self) -> ProviderCapabilityDTO:
        return self.capabilities

    def build_query(self, parameters: Dict[str, Any]) -> str:
        return self.query_builder.build(parameters)

    def get_knowledge(self) -> Dict[str, Any]:
        return self.knowledge
