import unittest
from worker.new.sdk.foundation.dto.models import DiscoveryProfileDTO
from worker.new.sdk.discovery.builders.search_generator import SearchGenerator
from worker.new.sdk.discovery.scheduling.scheduler import DiscoveryScheduler
from worker.new.sdk.foundation.events.bus import EventBus

class TestDiscoveryScheduler(unittest.TestCase):
    def setUp(self):
        EventBus.clear()
        self.generator = SearchGenerator()
        self.scheduler = DiscoveryScheduler(self.generator)

    def test_plan_and_dispatch(self):
        profile1 = DiscoveryProfileDTO(name="p1", provider="az", strategy="s", priority=10)
        profile2 = DiscoveryProfileDTO(name="p2", provider="az", strategy="s", priority=20)
        
        # Plan should sort by priority
        targets = self.scheduler.plan([profile1, profile2])
        self.assertEqual(len(targets), 2)
        self.assertEqual(targets[0].profile, "p2") # Higher priority
        self.assertEqual(targets[1].profile, "p1")
        
        # Dispatch should emit events
        events = []
        EventBus.subscribe("SearchTargetGenerated", lambda e: events.append(e))
        self.scheduler.dispatch(targets)
        
        self.assertEqual(len(events), 2)
        self.assertEqual(targets[0].state, "Queued")

if __name__ == '__main__':
    unittest.main()
