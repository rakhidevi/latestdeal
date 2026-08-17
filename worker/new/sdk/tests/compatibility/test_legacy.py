import unittest
from worker.new.sdk.foundation.dto.models import SearchTargetDTO
from worker.new.sdk.compatibility.legacy_extractor import LegacyExtractionAdapter
from worker.new.sdk.compatibility.legacy_publisher import LegacyPublisherAdapter
from worker.new.sdk.foundation.events.bus import EventBus, Event

class TestCompatibilityLayer(unittest.TestCase):
    def setUp(self):
        EventBus.clear()
        self.extractor = LegacyExtractionAdapter()
        self.publisher = LegacyPublisherAdapter()

    def test_legacy_extractor_translates_and_queues(self):
        events = []
        EventBus.subscribe("SearchTargetQueued", lambda e: events.append(e))
        
        target = SearchTargetDTO(
            trace_id="trc-test",
            provider="amazon",
            profile="loot",
            strategy="mrp",
            priority=10,
            budget_cost=5,
            url="http://test.com"
        )
        
        success = self.extractor.enqueue_target(target)
        self.assertTrue(success)
        self.assertEqual(len(events), 1)
        
        payload = events[0].payload["legacy_payload"]
        self.assertEqual(payload["url"], "http://test.com")
        self.assertEqual(payload["_trace_id"], "trc-test")

    def test_legacy_publisher_records_deals(self):
        events = []
        EventBus.subscribe("DealPublished", lambda e: events.append(e))
        
        legacy_deal = {"title": "Test", "affiliate_url": "http://aff.com"}
        result = self.publisher.record_legacy_publish(legacy_deal, trace_id="trc-legacy")
        
        self.assertTrue(result.success)
        self.assertEqual(result.published_url, "http://aff.com")
        self.assertEqual(len(events), 1)

if __name__ == '__main__':
    unittest.main()
