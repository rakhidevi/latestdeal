from sitestripe_scraper import get_sitestripe_link_and_data

class AffiliateService:
    @staticmethod
    def get_affiliate_link(provider: str, product_url: str) -> str:
        """
        Abstracts affiliate link generation based on the provider.
        Returns the shortened affiliate URL, or the raw URL on failure.
        """
        if provider.lower() == "amazon":
            try:
                data = get_sitestripe_link_and_data(product_url)
                if data and "sitestripe_url" in data and data["sitestripe_url"]:
                    return data["sitestripe_url"]
            except Exception as e:
                print(f"[AffiliateService] Amazon SiteStripe failed: {e}")
        
        # Add future providers like Flipkart here
        # elif provider.lower() == "flipkart":
        #    ...

        return product_url
