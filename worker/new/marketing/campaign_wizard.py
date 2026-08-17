from typing import List, Dict, Any

class CampaignWizard:
    """
    Automates the generation of Marketing Campaigns from Published Deals (Sprint 10).
    Maps Opportunity Scores to urgency language and applies template themes.
    """
    def __init__(self):
        self.templates = {
            "mrp_error": "🚨 PRICE MISTAKE ALERT! 🚨\n{title}\n\nPrice: ₹{price} (MRP: ₹{mrp}) - {discount}%\n\nBuy Now: {url}",
            "historical_low": "📉 LOWEST PRICE EVER! 📉\n{title}\n\nPrice drops to ₹{price} from ₹{mrp}\n\nGrab it: {url}"
        }
        
    def generate_campaign(self, published_ledger_entry: Dict[str, Any]) -> Dict[str, Any]:
        strategy = published_ledger_entry.get("strategy", "mrp_error")
        template = self.templates.get(strategy, self.templates["mrp_error"])
        
        payload = published_ledger_entry.get("final_payload", {})
        
        message = template.format(
            title=payload.get("title", "Premium Deal"),
            price=payload.get("price", "99"),
            mrp=payload.get("mrp", "999"),
            discount=payload.get("discount", "90"),
            url=published_ledger_entry.get("affiliate_url", "https://amazon.in")
        )
        
        return {
            "channels": ["telegram", "whatsapp"],
            "message_body": message,
            "scheduled_time": "immediate"
        }

if __name__ == "__main__":
    wizard = CampaignWizard()
    
    mock_ledger_entry = {
        "strategy": "mrp_error",
        "affiliate_url": "https://amazon.in/dp/mock?tag=ld21",
        "final_payload": {
            "title": "Samsung 65-inch QLED TV",
            "price": "29990",
            "mrp": "149990",
            "discount": "80"
        }
    }
    
    campaign = wizard.generate_campaign(mock_ledger_entry)
    print("=== Generated Marketing Campaign ===")
    print(f"Channels: {campaign['channels']}")
    print(f"Schedule: {campaign['scheduled_time']}")
    print("-" * 40)
    print(campaign["message_body"])
