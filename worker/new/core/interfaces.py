from abc import ABC, abstractmethod
from typing import Dict, Any, Optional
from .dtos import DealDTO

class BrowserProvider(ABC):
    """
    Abstract base class for browser management.
    Handles launching browsers, injecting cookies, managing proxies, and setting headers.
    """
    @abstractmethod
    def launch_browser(self):
        """Initializes and returns a browser instance/context."""
        pass
        
    @abstractmethod
    def cookies(self) -> list:
        """Returns required cookies for the session."""
        pass
        
    @abstractmethod
    def headers(self) -> Dict[str, str]:
        """Returns required HTTP headers."""
        pass
        
    @abstractmethod
    def proxy(self) -> Optional[Dict[str, str]]:
        """Returns proxy configuration if applicable."""
        pass

class StoreScraper(ABC):
    """
    Abstract base class for store-specific scraping logic.
    Every new store MUST implement this 8-step lifecycle contract.
    """
    @abstractmethod
    def initialize(self) -> None:
        """Sets up internal provider state and loads configurations."""
        pass

    @abstractmethod
    def authenticate(self) -> bool:
        """Handles authentication, session building, or CAPTCHA resolution."""
        pass

    @abstractmethod
    def search(self, query: str) -> list:
        """Executes a search query and returns raw provider results."""
        pass

    @abstractmethod
    def extract(self, html_content: str) -> Dict[str, Any]:
        """Extracts raw data blocks from the DOM."""
        pass

    @abstractmethod
    def normalize(self, raw_data: Dict[str, Any]) -> DealDTO:
        """Converts raw extracted data into standard immutable DealDTO."""
        pass

    @abstractmethod
    def validate(self, dto: DealDTO) -> bool:
        """Validates that the DTO meets the minimum requirements for ingestion."""
        pass

    @abstractmethod
    def health(self) -> Dict[str, Any]:
        """Returns detailed provider health matrix."""
        pass

    @abstractmethod
    def cleanup(self) -> None:
        """Closes browsers and frees resources."""
        pass
