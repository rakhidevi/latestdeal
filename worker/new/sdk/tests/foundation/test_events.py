import unittest
from worker.new.sdk.foundation.events.bus import EventBus, Event

class TestEventBus(unittest.TestCase):
    def setUp(self):
        EventBus.clear()

    def test_publish_and_subscribe(self):
        received_events = []

        def handler(event: Event):
            received_events.append(event)

        EventBus.subscribe("TestEvent", handler)
        
        test_event = Event(
            type="TestEvent",
            trace_id="trc-123",
            source="test_module",
            payload={"key": "value"}
        )
        
        EventBus.publish(test_event)

        self.assertEqual(len(received_events), 1)
        self.assertEqual(received_events[0].type, "TestEvent")
        self.assertEqual(received_events[0].payload["key"], "value")

    def test_unhandled_event(self):
        # Should not raise any exceptions
        test_event = Event(type="UnhandledEvent", trace_id="trc-123", source="test")
        EventBus.publish(test_event)

if __name__ == '__main__':
    unittest.main()
