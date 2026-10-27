# Amazon Creators API setup (India)

The local worker now has a small client in `creators_api.py` for Amazon India
`SearchItems`, `GetItems`, and `GetBrowseNodes`. It does not publish candidates
or claim that an offer is a genuine saving; those decisions must remain behind
LatestDeal's deal-quality checks.

## Access required

1. Enroll in Amazon Associates for the Amazon.in marketplace and confirm the
   account's tracking ID (Partner Tag).
2. Check Associates Central for the Creators API eligibility/access status.
   Amazon's current India documentation says access requires at least 10
   qualifying sales in the prior 30 days, registration, and API credentials.
3. Create Creators API credentials in Associates Central. Do not send the
   credential secret through chat or commit it to Git.
4. Put the credential ID, secret, and exact Partner Tag in `worker/.env`, using
   `worker/.env.example` as the template. The default credential version is
   3.2; use the version Amazon assigns, or set the documented token endpoint.

## Example (from the repository root)

```python
from dotenv import load_dotenv
from worker.creators_api import AmazonCreatorsAPI

load_dotenv("worker/.env", override=False)
client = AmazonCreatorsAPI.from_environment()
result = client.search_items(
    keywords="wireless headphones",
    search_index="Electronics",
    min_saving_percent=20,
)
print(result)
```

`SearchItems` returns at most ten results per request. Use Amazon browse nodes and
small, scheduled searches to cover categories. Refresh offer data with `get_items`
before publishing. Respect Amazon's current API terms, rate limits, field-specific
cache limits, price/availability timestamp and disclaimer requirements. In
particular, do not use API data as long-term price history unless Amazon confirms
that the intended retention and use are permitted.

## Current state

The API client is prepared, but it is not yet wired into the production deal
publishing pipeline. No credentials were found in the worker's environment, so
live API eligibility and API calls could not be confirmed. Keep the existing
ingestion flow unchanged until access is granted and a shadow-mode comparison
confirms the response mapping and approval rules.
