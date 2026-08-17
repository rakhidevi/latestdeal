from worker.new.sdk.foundation.dto.models import PluginManifestV2, DiscoveryEntrypoint

def get_amazon_manifest() -> PluginManifestV2:
    return PluginManifestV2(
        name="AmazonProvider",
        version="1.0.0",
        description="Official Reference Implementation for Amazon Commerce Discovery",
        author="System",
        supported_markets=["IN", "US", "UK"],
        supported_languages=["en", "hi"],
        supported_currency=["INR", "USD", "GBP"],
        supported_authentication=["None", "Cookie", "OAuth"],
        known_limitations=[
            "Captcha walls frequent on high pagination",
            "Node IDs vary strictly by marketplace (IN vs US)"
        ],
        supports_price_history=True,
        supports_reviews=True,
        supports_coupons=True,
        supports_images=True,
        supports_video=True,
        supports_inventory=True,
        supports_prime=True,
        supports_bank_offer=True,
        supports_lightning=True,
        supports_flash_sale=True,
        supports_bundles=True,
        supports_variants=True,
        supports_discount=True,
        supports_brand=True,
        supports_seller=True,
        supports_node=True,
        supports_price_band=True,
        supports_rating=True,
        entrypoints={
            "LIGHTNING": DiscoveryEntrypoint(
                intent="LIGHTNING",
                url="https://www.amazon.in/events/greatindianfestival",  # Using GIF/Deals page as example
                priority=90,
                expected_latency=3000,
                supports_pagination=True,
                max_pages=5
            ),
            "COUPONS": DiscoveryEntrypoint(
                intent="COUPONS",
                url="https://www.amazon.in/Coupons/b?node=10465704031",
                priority=80,
                expected_latency=2500,
                supports_pagination=True,
                max_pages=10
            ),
            "WAREHOUSE": DiscoveryEntrypoint(
                intent="WAREHOUSE",
                url="https://www.amazon.in/Amazon-Renewed/b?node=13349079031",
                priority=85,
                expected_latency=2000,
                supports_pagination=True,
                max_pages=5
            ),
            "BANK_OFFERS": DiscoveryEntrypoint(
                intent="BANK_OFFERS",
                url="https://www.amazon.in/bank-offers/b?node=bankoffers",
                priority=95,
                expected_latency=2500,
                supports_pagination=True,
                max_pages=3
            ),
            "BEST_SELLERS": DiscoveryEntrypoint(
                intent="BEST_SELLERS",
                url="https://www.amazon.in/gp/bestsellers",
                priority=75,
                expected_latency=2000,
                supports_pagination=True,
                max_pages=10
            ),
            "NEW_RELEASES": DiscoveryEntrypoint(
                intent="NEW_RELEASES",
                url="https://www.amazon.in/gp/new-releases",
                priority=70,
                expected_latency=2000,
                supports_pagination=True,
                max_pages=5
            ),
            "TRENDING": DiscoveryEntrypoint(
                intent="TRENDING",
                url="https://www.amazon.in/gp/movers-and-shakers",
                priority=80,
                expected_latency=2000,
                supports_pagination=True,
                max_pages=5
            ),
            "SUBSCRIBE": DiscoveryEntrypoint(
                intent="SUBSCRIBE",
                url="https://www.amazon.in/auto-deliveries/subscription/search",
                priority=65,
                expected_latency=2500,
                supports_pagination=True,
                max_pages=5
            ),
            "BUNDLES": DiscoveryEntrypoint(
                intent="BUNDLES",
                url="https://www.amazon.in/bundles",
                priority=70,
                expected_latency=2000,
                supports_pagination=True,
                max_pages=2
            ),
            "EXCHANGE": DiscoveryEntrypoint(
                intent="EXCHANGE",
                url="https://www.amazon.in/exchange",
                priority=85,
                expected_latency=2500,
                supports_pagination=True,
                max_pages=5
            )
        }
    )
