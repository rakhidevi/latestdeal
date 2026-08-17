import unittest
from worker.new.sdk.discovery.rules_engine.evaluator import RulesEvaluator

class TestRulesEvaluator(unittest.TestCase):
    def test_evaluate(self):
        evaluator = RulesEvaluator()
        
        rules = {
            "discount": {"gte": 90},
            "price": {"lt": 1000}
        }
        
        data_pass = {"discount": 95, "price": 500}
        data_fail_price = {"discount": 95, "price": 1500}
        data_fail_discount = {"discount": 80, "price": 500}
        data_missing = {"price": 500}
        
        self.assertTrue(evaluator.evaluate(rules, data_pass))
        self.assertFalse(evaluator.evaluate(rules, data_fail_price))
        self.assertFalse(evaluator.evaluate(rules, data_fail_discount))
        self.assertFalse(evaluator.evaluate(rules, data_missing))
        
        # Empty rules always pass
        self.assertTrue(evaluator.evaluate({}, data_pass))

if __name__ == '__main__':
    unittest.main()
