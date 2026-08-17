class DiscoveryStrategyException(Exception):
    """Base exception for all strategy-related errors."""
    pass

class StrategyConfigurationError(DiscoveryStrategyException):
    """Raised when a strategy is missing required configuration."""
    pass

class ProviderNotSupportedError(DiscoveryStrategyException):
    """Raised when a strategy is asked to run for an unsupported provider."""
    pass

class CapabilityNotSupportedError(DiscoveryStrategyException):
    """Raised when a strategy requires a capability the provider lacks."""
    pass
