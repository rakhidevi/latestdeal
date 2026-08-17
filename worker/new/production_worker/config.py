import os
import sys

# Production Worker Configuration

MAX_CONCURRENT_SCANS = int(os.getenv('MAX_CONCURRENT_SCANS', 5))
REQUEST_TIMEOUT = int(os.getenv('REQUEST_TIMEOUT', 15000))
NAVIGATION_TIMEOUT = int(os.getenv('NAVIGATION_TIMEOUT', 30000))
MAX_RETRIES = int(os.getenv('MAX_RETRIES', 3))
BACKOFF_BASE = int(os.getenv('BACKOFF_BASE', 2))
CONTEXT_RECYCLE_THRESHOLD = int(os.getenv('CONTEXT_RECYCLE_THRESHOLD', 30))

LARAVEL_API_URL = os.getenv('LARAVEL_API_URL')
LARAVEL_API_KEY = os.getenv('LARAVEL_API_KEY')
EDITORIAL_USER_ID = os.getenv('EDITORIAL_USER_ID')
AFFILIATE_STORE_ID = os.getenv('AFFILIATE_STORE_ID')

def validate_config():
    """Fail-fast configuration validation."""
    required = {
        'LARAVEL_API_URL': LARAVEL_API_URL,
        'LARAVEL_API_KEY': LARAVEL_API_KEY,
        'EDITORIAL_USER_ID': EDITORIAL_USER_ID,
        'AFFILIATE_STORE_ID': AFFILIATE_STORE_ID
    }
    
    missing = [k for k, v in required.items() if not v]
    
    if missing:
        # In a real production deployment, this blocks startup.
        # For local dev/dry-run, we'll just print a warning but we won't strictly exit 
        # unless we are not in DRY_RUN mode.
        is_dry_run = os.getenv('DRY_RUN', '1') == '1'
        if not is_dry_run:
            print(f"CRITICAL: Missing required configuration: {', '.join(missing)}")
            sys.exit(1)
        else:
            print(f"WARNING (DRY_RUN): Missing config {missing}, continuing anyway.")

# Run validation on import
validate_config()
