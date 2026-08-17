import unittest
from worker.new.sdk.publishing.ledger import PublishingLedger, LedgerEntry

class TestPublishingLedger(unittest.TestCase):
    def setUp(self):
        self.ledger = PublishingLedger()

    def test_append_and_get_history(self):
        entry1 = LedgerEntry(
            event_type="DealPublished",
            deal_id="deal-1",
            version=1,
            trace_id="trc-1",
            worker="worker-1",
            provider="amazon",
            profile="loot",
            strategy="mrp",
            payload_after={"price": 100},
            status="Published",
            duration_ms=1500
        )
        self.ledger.append(entry1)
        
        history = self.ledger.get_history("deal-1")
        self.assertEqual(len(history), 1)
        self.assertEqual(history[0].version, 1)

    def test_get_latest_version(self):
        entry1 = LedgerEntry(
            event_type="DealPublished",
            deal_id="deal-1",
            version=1,
            trace_id="trc-1",
            worker="w-1",
            provider="az",
            profile="l",
            strategy="m",
            payload_after={},
            status="Pub",
            duration_ms=10
        )
        entry2 = LedgerEntry(
            event_type="DealUpdated",
            deal_id="deal-1",
            version=2,
            trace_id="trc-2",
            worker="w-1",
            provider="az",
            profile="l",
            strategy="m",
            payload_after={},
            status="Pub",
            duration_ms=10
        )
        self.ledger.append(entry1)
        self.ledger.append(entry2)
        
        self.assertEqual(self.ledger.get_latest_version("deal-1"), 2)
        self.assertEqual(self.ledger.get_latest_version("deal-unknown"), 0)

if __name__ == '__main__':
    unittest.main()
