from sitestripe_scraper import get_sitestripe_link_and_data

class AffiliateService:
    @staticmethod
    def get_affiliate_link(provider: str, product_url: str, deal=None) -> str:
        """
        Abstracts affiliate link generation based on the provider.
        Returns the shortened affiliate URL, or the raw URL on failure.
        Transfers Rufus AI price history to deal if available.
        """
        if provider.lower() == "amazon":
            try:
                data = get_sitestripe_link_and_data(product_url)
                if data:
                    if deal and data.get("price_history"):
                        deal.price_history_raw = data.get("price_history")
                    if "sitestripe_url" in data and data["sitestripe_url"]:
                        return data["sitestripe_url"]
            except Exception as e:
                print(f"[AffiliateService] Amazon SiteStripe failed: {e}")
        
        # Add future providers like Flipkart here
        # elif provider.lower() == "flipkart":
        #    ...

        return product_url
