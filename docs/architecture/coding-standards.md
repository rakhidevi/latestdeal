# Coding Standards

## 1. Interface Freezing (ABCs)
All Python plugins MUST inherit from established Abstract Base Classes (e.g., `StoreScraper`). Duck-typing for core providers is prohibited.

## 2. Immutable Data (DTOs)
Passing native dictionaries `dict` across module boundaries is prohibited. Data must be validated and packed into a `dataclass` or `pydantic` model (e.g., `DealDTO`) at the boundary.

## 3. Standardized Errors
Use custom exception hierarchies (`ProviderError`, `ParsingError`) instead of catching/raising `Exception`.

## 4. No Implicit State
All functions should be pure where possible. Providers should not maintain implicit browser state between independent `extract()` calls.

## 5. Event Correlation
Every Laravel event must inherit a `correlation_id` from its parent. If a `DealDiscovered` event generates a `DealScored` event, the correlation ID must be passed down.
