import unittest
from worker.new.sdk.foundation.dto.models import PluginManifestV2
from worker.new.sdk.discovery.engine.capability_matrix import CapabilityMatrix
from worker.new.sdk.discovery.engine.constraint_engine import ConstraintEngine

class TestProviderCapabilities(unittest.TestCase):
    def setUp(self):
        self.capability_matrix = CapabilityMatrix()
        
        # Mock Amazon Provider Manifest
        self.amazon_manifest = PluginManifestV2(
            name="amazon",
            version="1.0.0",
            supports_discount=True,
            supports_brand=True,
            supports_coupon=True,
            supports_prime=True,
            supports_node=True
        )
        
        # Mock Flipkart Provider Manifest
        self.flipkart_manifest = PluginManifestV2(
            name="flipkart",
            version="1.0.0",
            supports_discount=True,
            supports_brand=True,
            supports_coupon=False,
            supports_prime=False,
            supports_node=True
        )
        
        self.capability_matrix.register_manifest(self.amazon_manifest)
        self.capability_matrix.register_manifest(self.flipkart_manifest)
        self.constraint_engine = ConstraintEngine(self.capability_matrix)

    def test_amazon_capabilities(self):
        self.assertTrue(self.capability_matrix.supports("amazon", "discount"))
        self.assertTrue(self.capability_matrix.supports("amazon", "prime"))
        self.assertTrue(self.capability_matrix.supports("amazon", "coupon"))
        self.assertFalse(self.capability_matrix.supports("amazon", "seller")) # False by default

    def test_flipkart_capabilities(self):
        self.assertTrue(self.capability_matrix.supports("flipkart", "discount"))
        self.assertFalse(self.capability_matrix.supports("flipkart", "prime"))
        self.assertFalse(self.capability_matrix.supports("flipkart", "coupon"))

    def test_constraint_engine_valid_permutations(self):
        # Generate some permutations
        perms = [
            {"brand": "Samsung", "is_prime": True},
            {"brand": "Samsung", "is_prime": False, "discount_min": 50},
            {"brand": "Apple", "coupon_only": True}
        ]
        
        # Amazon supports everything required here
        amazon_valid = self.constraint_engine.filter_valid_permutations("amazon", perms)
        self.assertEqual(len(amazon_valid), 3)
        
        # Flipkart does not support prime or coupon
        flipkart_valid = self.constraint_engine.filter_valid_permutations("flipkart", perms)
        self.assertEqual(len(flipkart_valid), 1)
        self.assertEqual(flipkart_valid[0]["brand"], "Samsung")
        self.assertEqual(flipkart_valid[0]["is_prime"], False)

if __name__ == '__main__':
    unittest.main()
