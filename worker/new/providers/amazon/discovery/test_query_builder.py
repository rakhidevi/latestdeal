import unittest
from worker.new.providers.amazon.discovery.query_builder import AmazonQueryBuilder

class TestAmazonQueryBuilder(unittest.TestCase):
    
    def test_basic_keyword_search(self):
        builder = AmazonQueryBuilder()
        url = builder.with_keyword("samsung phone").build()
        self.assertEqual(url, "https://www.amazon.in/s?k=samsung+phone")
        
    def test_complex_rh_query(self):
        builder = AmazonQueryBuilder()
        url = (builder
               .with_keyword("smart tv")
               .with_brands(["Samsung", "LG"])
               .with_node("12345")
               .with_prime()
               .with_discount(50)
               .build())
               
        # Expected RH string: p_89:Samsung|LG,n:12345,p_n_free_shipping_eligible:2049110031,p_8:50-
        self.assertIn("p_89:Samsung|LG", url)
        self.assertIn("n:12345", url)
        self.assertIn("p_n_free_shipping_eligible", url)
        self.assertIn("p_8:50-", url)
        
    def test_sort_by(self):
        builder = AmazonQueryBuilder()
        url = builder.with_keyword("shoes").sort_by_discount().build()
        self.assertIn("s=discount-desc-rank", url)

if __name__ == '__main__':
    unittest.main()
