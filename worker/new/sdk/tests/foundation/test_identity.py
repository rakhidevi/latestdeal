import unittest
from worker.new.sdk.foundation.identity.generator import generate_uuid, generate_trace_id

class TestIdentityGenerator(unittest.TestCase):
    def test_generate_uuid(self):
        uid = generate_uuid()
        self.assertIsInstance(uid, str)
        self.assertEqual(len(uid), 36)

    def test_generate_trace_id_no_prefix(self):
        trace_id = generate_trace_id()
        self.assertTrue(trace_id.startswith("trc-"))
        parts = trace_id.split("-")
        self.assertEqual(len(parts), 3) # trc, timestamp, short_uuid

    def test_generate_trace_id_with_prefix(self):
        trace_id = generate_trace_id("disc")
        self.assertTrue(trace_id.startswith("disc-"))
        parts = trace_id.split("-")
        self.assertEqual(len(parts), 3)

if __name__ == '__main__':
    unittest.main()
