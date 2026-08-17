from worker.new.sdk.foundation.dto.models import PluginManifestV2

def get_manifest() -> PluginManifestV2:
    return PluginManifestV2(
        name="FlipkartProvider",
        version="1.0.0",
        description="Official Reference Implementation for Flipkart Commerce Discovery",
        author="System",
        supported_markets=["IN"],
        supported_languages=["en", "hi"],
        supported_currency=["INR"],
        supported_authentication=["None", "Cookie"],
        known_limitations=[
            "Frequent CAPTCHA walls",
            "Stricter rate limits than Amazon"
        ],
        supports_price_history=False,
        supports_reviews=True,
        supports_coupons=False,
        supports_images=True,
        supports_video=False,
        supports_inventory=True,
        supports_prime=False, # Amazon specific
        supports_bank_offer=True,
        supports_lightning=False,
        supports_flash_sale=True,
        supports_bundles=True,
        supports_variants=True,
        supports_discount=True,
        supports_brand=True,
        supports_seller=True,
        supports_node=True,
        supports_price_band=True,
        supports_rating=True
    )
