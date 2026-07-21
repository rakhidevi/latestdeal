# Provider Architecture (Python)

## Directory Structure
Providers must be completely isolated.
```
worker/new/providers/
  amazon/
    scraper.py
    parser.py
    selectors.py
    config.py
    models.py
```

## Abstract Base Contract
Every provider must implement the full `StoreScraper` lifecycle:

```python
class StoreScraper(ABC):
    @abstractmethod
    def initialize(self): ...
    @abstractmethod
    def authenticate(self): ...
    @abstractmethod
    def search(self, query: str): ...
    @abstractmethod
    def extract(self, html: str): ...
    @abstractmethod
    def normalize(self, raw: dict): ...
    @abstractmethod
    def validate(self, dto: DealDTO): ...
    @abstractmethod
    def health(self): ...
    @abstractmethod
    def cleanup(self): ...
```

## Data Transfer Objects (DTOs)
Raw dictionaries are strictly prohibited across module boundaries. All data must be wrapped in immutable DTOs (e.g., `DealDTO`, `ProductDTO`, `PriceDTO`, `CouponDTO`).

## Provider Versioning
Every provider must expose metadata for debugging DOM changes:
```python
VERSION = "1.2.0"
SELECTORS_UPDATED = "2026-07"
PARSER_VERSION = "v3"
SCHEMA_VERSION = 1
```

## Standardized Error Hierarchy
Providers must only raise standardized exceptions:
- `ProviderError`
- `BrowserError`
- `SelectorError`
- `AuthenticationError`
- `RateLimitError`
- `ParsingError`
- `ValidationError`
