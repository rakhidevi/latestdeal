"""Minimal Amazon Creators API client for Amazon India.

Credentials are read from the worker environment and must never be committed.
This client returns Amazon's response data; callers must apply LatestDeal's
own deal-quality checks before publishing any product.
"""

from __future__ import annotations

import os
import threading
import time
from typing import Any

import requests


class CreatorsAPIError(RuntimeError):
    """Raised when Creators API credentials or a request are invalid."""


class AmazonCreatorsAPI:
    API_BASE = "https://creatorsapi.amazon"
    TOKEN_ENDPOINTS = {
        "3.1": "https://api.amazon.com/auth/o2/token",
        "3.2": "https://api.amazon.co.uk/auth/o2/token",
        "3.3": "https://api.amazon.co.jp/auth/o2/token",
    }
    MARKETPLACE = "www.amazon.in"

    DEFAULT_RESOURCES = [
        "itemInfo.title",
        "itemInfo.byLineInfo",
        "browseNodeInfo.browseNodes",
        "offersV2.listings.availability",
        "offersV2.listings.condition",
        "offersV2.listings.dealDetails",
        "offersV2.listings.merchantInfo",
        "offersV2.listings.price",
        "offersV2.listings.type",
    ]

    def __init__(self, *, session: requests.Session | None = None) -> None:
        self.credential_id = os.getenv("AMAZON_CREATORS_CREDENTIAL_ID", "").strip()
        self.credential_secret = os.getenv("AMAZON_CREATORS_CREDENTIAL_SECRET", "").strip()
        self.partner_tag = os.getenv("AMAZON_CREATORS_PARTNER_TAG", "").strip()
        self.credential_version = os.getenv("AMAZON_CREATORS_CREDENTIAL_VERSION", "3.2").strip()
        self.token_endpoint = os.getenv("AMAZON_CREATORS_TOKEN_ENDPOINT", "").strip()
        self.marketplace = os.getenv("AMAZON_CREATORS_MARKETPLACE", self.MARKETPLACE).strip()
        self.session = session or requests.Session()
        self._token = ""
        self._token_expires_at = 0.0
        self._token_lock = threading.Lock()

    @classmethod
    def from_environment(cls) -> "AmazonCreatorsAPI":
        """Construct a configured client or fail with safe setup guidance."""
        client = cls()
        missing = [
            name for name, value in (
                ("AMAZON_CREATORS_CREDENTIAL_ID", client.credential_id),
                ("AMAZON_CREATORS_CREDENTIAL_SECRET", client.credential_secret),
                ("AMAZON_CREATORS_PARTNER_TAG", client.partner_tag),
            ) if not value
        ]
        if missing:
            raise CreatorsAPIError(
                "Creators API is not configured. Set these worker environment variables: "
                + ", ".join(missing)
            )
        if client.credential_version not in cls.TOKEN_ENDPOINTS and not client.token_endpoint:
            raise CreatorsAPIError(
                "Set AMAZON_CREATORS_CREDENTIAL_VERSION to 3.1, 3.2, or 3.3, "
                "or provide AMAZON_CREATORS_TOKEN_ENDPOINT."
            )
        return client

    def _get_token(self) -> str:
        with self._token_lock:
            if self._token and time.time() < self._token_expires_at - 60:
                return self._token

            endpoint = self.token_endpoint or self.TOKEN_ENDPOINTS[self.credential_version]
            try:
                response = self.session.post(
                    endpoint,
                    json={
                        "grant_type": "client_credentials",
                        "client_id": self.credential_id,
                        "client_secret": self.credential_secret,
                        "scope": "creatorsapi::default",
                    },
                    headers={"Content-Type": "application/json"},
                    timeout=20,
                )
                response.raise_for_status()
                body = response.json()
            except (requests.RequestException, ValueError) as exc:
                raise CreatorsAPIError(f"Could not obtain Creators API token: {exc}") from exc

            token = body.get("access_token")
            if not token:
                raise CreatorsAPIError("Amazon token response did not include an access token.")
            self._token = str(token)
            self._token_expires_at = time.time() + int(body.get("expires_in", 3600))
            return self._token

    def _post(self, operation: str, payload: dict[str, Any]) -> dict[str, Any]:
        request_body = {
            **payload,
            "marketplace": self.marketplace,
            "partnerTag": self.partner_tag,
        }
        url = f"{self.API_BASE}/catalog/v1/{operation}"
        last_error: Exception | None = None

        for attempt in range(3):
            try:
                response = self.session.post(
                    url,
                    json=request_body,
                    headers={
                        "Authorization": f"Bearer {self._get_token()}",
                        "Content-Type": "application/json",
                        "x-marketplace": self.marketplace,
                    },
                    timeout=30,
                )
                if response.status_code == 401 and attempt == 0:
                    self._token = ""
                    self._token_expires_at = 0
                    continue
                if response.status_code == 429 or response.status_code >= 500:
                    if attempt < 2:
                        retry_after = response.headers.get("Retry-After", "")
                        try:
                            delay = min(max(float(retry_after), 1), 30) if retry_after else 2 ** attempt
                        except ValueError:
                            delay = 2 ** attempt
                        time.sleep(delay)
                        continue
                response.raise_for_status()
                result = response.json()
                if result.get("errors"):
                    raise CreatorsAPIError(f"Amazon returned item/query errors: {result['errors']}")
                return result
            except CreatorsAPIError:
                raise
            except (requests.RequestException, ValueError) as exc:
                last_error = exc
                if attempt < 2:
                    time.sleep(2 ** attempt)

        raise CreatorsAPIError(f"Creators API request failed after retries: {last_error}")

    def search_items(
        self,
        *,
        keywords: str,
        search_index: str = "All",
        browse_node_id: str | None = None,
        brand: str | None = None,
        min_saving_percent: int | None = None,
        item_count: int = 10,
    ) -> dict[str, Any]:
        """Search Amazon India. Amazon limits SearchItems to ten results/call."""
        if not keywords.strip():
            raise ValueError("keywords must not be empty")
        if not 1 <= item_count <= 10:
            raise ValueError("item_count must be between 1 and 10")
        if min_saving_percent is not None and not 1 <= min_saving_percent < 100:
            raise ValueError("min_saving_percent must be between 1 and 99")

        payload: dict[str, Any] = {
            "keywords": keywords.strip(),
            "searchIndex": search_index,
            "itemCount": item_count,
            "resources": self.DEFAULT_RESOURCES,
        }
        if browse_node_id:
            payload["browseNodeId"] = str(browse_node_id)
        if brand:
            payload["brand"] = brand.strip()
        if min_saving_percent is not None:
            payload["minSavingPercent"] = min_saving_percent
        return self._post("searchItems", payload)

    def get_items(self, asins: list[str]) -> dict[str, Any]:
        """Refresh offer data for up to ten ASINs before LatestDeal review."""
        clean_asins = list(dict.fromkeys(asin.strip().upper() for asin in asins if asin.strip()))
        if not clean_asins or len(clean_asins) > 10:
            raise ValueError("Provide between 1 and 10 ASINs")
        return self._post("getItems", {
            "itemIds": clean_asins,
            "itemIdType": "ASIN",
            "resources": self.DEFAULT_RESOURCES,
        })

    def get_browse_nodes(self, browse_node_ids: list[str]) -> dict[str, Any]:
        """Resolve up to ten Amazon browse nodes for category mapping."""
        clean_ids = list(dict.fromkeys(str(node_id).strip() for node_id in browse_node_ids if str(node_id).strip()))
        if not clean_ids or len(clean_ids) > 10:
            raise ValueError("Provide between 1 and 10 browse node IDs")
        return self._post("getBrowseNodes", {"browseNodeIds": clean_ids})
