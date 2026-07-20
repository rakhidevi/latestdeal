import time
import random

class SessionManager:
    """Manages human-like interactions and session state."""
    
    @staticmethod
    def simulate_human_scroll(page, scroll_count=1):
        if not page: return
        for _ in range(scroll_count):
            page.mouse.wheel(0, random.randint(300, 800))
            time.sleep(random.uniform(1.0, 3.0))
