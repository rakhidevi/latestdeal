class ProfileValidator:
    def __init__(self, active_profile: dict = None):
        self.profile = active_profile or {}

    def validate(self, deal: dict) -> dict:
        """
        Validates the deal against the current active discovery profile constraints.
        Returns a dict containing validation_status and validation_checks.
        """
        checks = {}
        passed = True

        # 1. Brand Constraint
        brand_data = self.profile.get('brand')
        expected_brand = brand_data.get('name') if isinstance(brand_data, dict) else brand_data
        if expected_brand:
            actual_brand = deal.get('resolved_brand_name')
            brand_pass = (expected_brand.lower() == str(actual_brand).lower()) if actual_brand else False
            checks['brand'] = {
                'expected': expected_brand,
                'actual': actual_brand,
                'passed': brand_pass
            }
            if not brand_pass:
                passed = False

        # 2. Product Type / Accessory Constraint
        is_accessory = deal.get('is_accessory', False)
        actual_product_type = deal.get('product_type')
        
        # If it's an accessory, automatically reject unless the profile is explicitly searching for accessories
        category_data = self.profile.get('category')
        expected_category = category_data.get('name') if isinstance(category_data, dict) else category_data
        
        is_accessory_expected = expected_category and 'accessor' in expected_category.lower()
        
        if is_accessory and not is_accessory_expected:
            checks['product_intent'] = {
                'expected': 'Main Product',
                'actual': 'Accessory',
                'passed': False
            }
            passed = False
        else:
            checks['product_intent'] = {
                'expected': 'Main Product',
                'actual': actual_product_type or 'Main Product',
                'passed': True
            }

        # 3. Category Constraint
        if expected_category and str(expected_category).lower() != 'all':
            actual_categories = deal.get('category_names', []) # Assuming taxonomy classifier populates this for validation
            if not isinstance(actual_categories, list):
                actual_categories = []
            
            # Add product_type to categories to make matching more robust (e.g. "TV")
            if actual_product_type and actual_product_type not in actual_categories:
                actual_categories.append(actual_product_type)
                
            # Match if the expected category is in any of the resolved categories
            category_pass = any(expected_category.lower() == str(c).lower() for c in actual_categories)
            checks['category'] = {
                'expected': expected_category,
                'actual': actual_categories,
                'passed': category_pass
            }
            if not category_pass:
                passed = False

        # 3. Discount Constraint
        expected_discount = self.profile.get('min_discount_percent')
        if expected_discount is not None:
            price_intel = deal.get('price_intelligence', {})
            actual_discount = price_intel.get('calculated_discount')
            
            if actual_discount is None:
                discount_pass = False
            else:
                discount_pass = actual_discount >= float(expected_discount)
                
            checks['discount'] = {
                'required': expected_discount,
                'actual': actual_discount,
                'passed': discount_pass
            }
            if not discount_pass:
                passed = False

        # 4. Maximum Price Constraint
        max_price = self.profile.get('max_price')
        if max_price is not None:
            actual_price = deal.get('discounted_price', 0)
            price_pass = actual_price <= float(max_price)
            checks['price'] = {
                'max_allowed': max_price,
                'actual': actual_price,
                'passed': price_pass
            }
            if not price_pass:
                passed = False

        return {
            'validation_status': 'PASS' if passed else 'FAIL',
            'validation_checks': checks
        }
