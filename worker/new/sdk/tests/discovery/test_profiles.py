import unittest
import os
import yaml
import tempfile
from worker.new.sdk.discovery.profiles.loader import ProfileLoader

class TestProfiles(unittest.TestCase):
    def test_load_from_file(self):
        # Create a temporary yaml file
        data = {
            "schema": 1,
            "name": "Test Profile",
            "provider": "amazon",
            "strategy": "mrp",
            "priority": 10
        }
        with tempfile.NamedTemporaryFile(mode='w', delete=False, suffix='.yaml') as f:
            yaml.dump(data, f)
            filepath = f.name
            
        try:
            profile = ProfileLoader.load_from_file(filepath)
            self.assertEqual(profile.name, "Test Profile")
            self.assertEqual(profile.provider, "amazon")
        finally:
            os.unlink(filepath)

if __name__ == '__main__':
    unittest.main()
