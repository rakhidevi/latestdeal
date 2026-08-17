import unittest
from worker.new.sdk.dashboard.dto import DiscoveryDashboardDTO, PublishingDashboardDTO

class TestDashboardDTOs(unittest.TestCase):
    def test_discovery_dashboard(self):
        dto = DiscoveryDashboardDTO(profiles_running=5, search_targets_generated=100)
        self.assertEqual(dto.profiles_running, 5)
        self.assertEqual(dto.extraction_rate_percent, 0.0)

    def test_publishing_dashboard(self):
        dto = PublishingDashboardDTO(published_today=10, rejected_today=2)
        self.assertEqual(dto.published_today, 10)
        self.assertEqual(dto.rollbacks_today, 0)

if __name__ == '__main__':
    unittest.main()
