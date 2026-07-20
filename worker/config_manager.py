import json
import os

CONFIG_PATH = os.path.join(os.path.dirname(__file__), 'config.json')

def load_config():
    if not os.path.exists(CONFIG_PATH):
        return {
            "telegram": {"target_channels": []},
            "scraper": {"ignore_domains": ["amazon.in/storefront", "youtube.com", "youtu.be", "t.me", "telegram.me"]}
        }
    with open(CONFIG_PATH, 'r') as f:
        return json.load(f)

config = load_config()
