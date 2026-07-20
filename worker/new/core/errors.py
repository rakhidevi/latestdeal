class PlatformError(Exception):
    """Base exception for all LatestDeal errors."""
    pass

class ProviderError(PlatformError):
    """General provider failure."""
    pass

class BrowserError(PlatformError):
    """Browser failed to launch or crashed."""
    pass

class SelectorError(PlatformError):
    """DOM changed, selector failed to match."""
    pass

class AuthenticationError(PlatformError):
    """Failed to authenticate or session expired."""
    pass

class RateLimitError(PlatformError):
    """Blocked or throttled by provider."""
    pass

class ParsingError(PlatformError):
    """Failed to parse extracted data (e.g., regex failure)."""
    pass

class ValidationError(PlatformError):
    """Extracted data failed schema validation."""
    pass
