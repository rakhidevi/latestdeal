class DealValidator:
    @staticmethod
    def validate(deal_data: dict) -> bool:
        """
        Validates whether the extracted deal meets the mathematical and trust thresholds.
        """
        if not deal_data:
            return False
            
        price = deal_data.get('price', 0)
        mrp = deal_data.get('mrp', 0)
        
        if price <= 0 or mrp <= 0:
            return False
            
        discount_percentage = ((mrp - price) / mrp) * 100
        
        # Example validation: Must be at least 10% discount
        if discount_percentage < 10.0:
            return False
            
        # Example validation: Price ratio cannot be absurd (e.g. MRP is 20x price)
        if mrp / price > 20:
            return False
            
        return True
