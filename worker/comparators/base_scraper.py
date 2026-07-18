from abc import ABC, abstractmethod
import urllib.parse

class BaseScraper(ABC):
    def __init__(self, page):
        self.page = page

    @property
    @abstractmethod
    def store_name(self) -> str:
        pass

    @abstractmethod
    async def search(self, title: str) -> dict:
        """
        Searches the store for the title and returns a dictionary:
        {
            "store": str,
            "price": float,
            "url": str,
            "delivery": str,
            "rating": str
        }
        """
        pass
