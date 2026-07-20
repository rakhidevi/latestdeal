import os
import uuid

class ScreenshotService:
    """Captures and stores debug screenshots."""
    
    @staticmethod
    def capture(page, name: str = "debug"):
        if not page: return ""
        try:
            filename = f"{name}_{uuid.uuid4().hex[:8]}.png"
            path = os.path.join(os.path.dirname(__file__), '..', '..', '..', '..', '..', 'scratch', filename)
            os.makedirs(os.path.dirname(path), exist_ok=True)
            page.screenshot(path=path)
            return path
        except Exception:
            return ""
