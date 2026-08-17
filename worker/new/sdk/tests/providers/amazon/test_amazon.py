import unittest
from worker.new.sdk.providers.amazon import AmazonProvider

class TestAmazonProvider(unittest.TestCase):
    def setUp(self):
        self.provider = AmazonProvider()

    def test_get_capabilities(self):
        caps = self.provider.get_capabilities()
        self.assertEqual(caps.name, "amazon")
        self.assertTrue("brand" in caps.supported_filters)

    def test_build_query(self):
        url = self.provider.build_query({"keyword": "laptop", "brand": "HP", "discount_min": 50, "page": 2})
        self.assertTrue("k=laptop" in url)
        self.assertTrue("p_89:HP" in url)
        self.assertTrue("p_8:50-" in url)
        self.assertTrue("page=2" in url)

if __name__ == '__main__':
    unittest.main()
