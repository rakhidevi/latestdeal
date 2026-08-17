import unittest
from worker.new.sdk.discovery.registry.provider import ProviderRegistry
from worker.new.sdk.discovery.registry.capability import CapabilityRegistry
from worker.new.sdk.foundation.contracts.provider import CommerceProvider
from worker.new.sdk.foundation.dto.models import ProviderCapabilityDTO

class MockProvider(CommerceProvider):
    def get_capabilities(self): pass
    def build_query(self, p): pass
    def get_knowledge(self): pass

class TestRegistries(unittest.TestCase):
    def test_provider_registry(self):
        ProviderRegistry.register("mock", MockProvider())
        self.assertIsNotNone(ProviderRegistry.get("mock"))

    def test_capability_registry(self):
        cap = ProviderCapabilityDTO(name="test", version="1.0")
        CapabilityRegistry.register("mock", cap)
        self.assertEqual(CapabilityRegistry.get("mock").version, "1.0")

if __name__ == '__main__':
    unittest.main()
