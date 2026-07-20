class CaptchaDetector:
    """Detects CAPTCHAs and anti-bot blocks across various providers."""
    
    @staticmethod
    def is_captcha_present(page, provider_name: str = "") -> bool:
        if not page:
            return False
            
        title = page.title().lower()
        if "captcha" in title or "robot" in title:
            return True
            
        if "amazon" in provider_name.lower():
            if page.locator("form[action='/errors/validateCaptcha']").count() > 0:
                return True
                
        return False
