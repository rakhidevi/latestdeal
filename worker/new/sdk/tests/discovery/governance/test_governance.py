import unittest
from worker.new.sdk.discovery.governance.manager import DecisionGovernanceManager

class TestDecisionGovernanceManager(unittest.TestCase):
    def setUp(self):
        self.manager = DecisionGovernanceManager()
        
    def test_create_and_approve_policy(self):
        # Create V1
        rules_v1 = {"publish_threshold": 85}
        policy = self.manager.create_policy_version("1.0.0", rules_v1, "admin")
        self.assertEqual(policy.status, "DRAFT")
        
        # Approve V1
        self.manager.approve_policy("1.0.0", "superadmin")
        active = self.manager.get_active_policy()
        self.assertIsNotNone(active)
        self.assertEqual(active.version, "1.0.0")
        self.assertEqual(active.status, "ACTIVE")
        
    def test_policy_rollback(self):
        # Setup V1
        self.manager.create_policy_version("1.0.0", {"t": 80}, "admin")
        self.manager.approve_policy("1.0.0", "superadmin")
        
        # Setup V2
        self.manager.create_policy_version("2.0.0", {"t": 90}, "admin")
        self.manager.approve_policy("2.0.0", "superadmin")
        
        # Check active is V2 and V1 is DEPRECATED
        self.assertEqual(self.manager.get_active_policy().version, "2.0.0")
        self.assertEqual(self.manager._policies["1.0.0"].status, "DEPRECATED")
        
        # Rollback to V1
        self.manager.rollback_policy("1.0.0", "Reverting strict threshold")
        
        # Check V1 is active, V2 is ARCHIVED
        self.assertEqual(self.manager.get_active_policy().version, "1.0.0")
        self.assertEqual(self.manager._policies["2.0.0"].status, "ARCHIVED")

if __name__ == '__main__':
    unittest.main()
