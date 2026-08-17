from typing import List
from worker.models import Deal
from worker.new.sdk.foundation.dto.models import EvidenceRecord, EvidenceType, EvidenceSource, TraceContext

class EvidenceBuilder:
    """
    Translates a legacy Deal object into a deterministic EvidenceGraph
    for the OpportunityEngine to process.
    """
    
    @staticmethod
    def build(deal: Deal, trace_context: TraceContext) -> List[EvidenceRecord]:
        graph: List[EvidenceRecord] = []
        
        strategy = "LegacyScraperAdapter"
        
        # 1. PRICE Evidence
        # If there's a valid price and original price, compute weight based on discount depth
        if deal.price is not None and deal.original_price is not None and deal.original_price > 0:
            if deal.price < deal.original_price:
                discount_raw = ((deal.original_price - deal.price) / deal.original_price) * 100
                weight = min(int(discount_raw), 100) # Weight scales with discount (up to 100)
                
                graph.append(EvidenceRecord(
                    trace_context=trace_context,
                    strategy=strategy,
                    type=EvidenceType.PRICE,
                    weight=weight,
                    confidence=0.9, # High confidence since it's directly extracted
                    source=EvidenceSource.PROVIDER,
                    metadata={"price": deal.price, "mrp": deal.original_price, "computed_discount": discount_raw}
                ))
            elif deal.price >= deal.original_price:
                # No discount or overpriced - negative evidence
                graph.append(EvidenceRecord(
                    trace_context=trace_context,
                    strategy=strategy,
                    type=EvidenceType.PRICE,
                    weight=-50,
                    confidence=0.9,
                    source=EvidenceSource.PROVIDER,
                    metadata={"price": deal.price, "mrp": deal.original_price}
                ))
        
        # 2. PROMOTION Evidence (Coupons)
        if deal.coupon:
            graph.append(EvidenceRecord(
                trace_context=trace_context,
                strategy=strategy,
                type=EvidenceType.PROMOTION,
                weight=30, # Coupons add solid value
                confidence=0.95,
                source=EvidenceSource.PROVIDER,
                metadata={"coupon": deal.coupon}
            ))
            
        # 3. BRAND Evidence
        if deal.brand and deal.brand.lower() not in ["unknown", "generic"]:
            graph.append(EvidenceRecord(
                trace_context=trace_context,
                strategy=strategy,
                type=EvidenceType.BRAND,
                weight=25, # Recognized brand adds trust
                confidence=0.8,
                source=EvidenceSource.PROVIDER,
                metadata={"brand": deal.brand}
            ))
            
        # 4. AVAILABILITY Evidence
        if deal.availability:
            # If explicit out of stock string is present, deal pipeline usually rejects it before this point, 
            # but we encode it just in case.
            is_in_stock = "out of stock" not in deal.availability.lower() and "unavailable" not in deal.availability.lower()
            if is_in_stock:
                graph.append(EvidenceRecord(
                    trace_context=trace_context,
                    strategy=strategy,
                    type=EvidenceType.AVAILABILITY,
                    weight=10,
                    confidence=0.9,
                    source=EvidenceSource.PROVIDER,
                    metadata={"availability": deal.availability}
                ))
            else:
                graph.append(EvidenceRecord(
                    trace_context=trace_context,
                    strategy=strategy,
                    type=EvidenceType.AVAILABILITY,
                    weight=-100, # Massive penalty for OOS
                    confidence=1.0,
                    source=EvidenceSource.PROVIDER,
                    metadata={"availability": deal.availability}
                ))
                
        # 5. RATING Evidence
        if deal.rating:
            # Assume 5.0 scale
            if deal.rating >= 4.0:
                graph.append(EvidenceRecord(
                    trace_context=trace_context,
                    strategy=strategy,
                    type=EvidenceType.CUSTOM,
                    weight=15,
                    confidence=0.8,
                    source=EvidenceSource.PROVIDER,
                    metadata={"rating": deal.rating, "sub_type": "RATING"}
                ))
            elif deal.rating < 3.0:
                graph.append(EvidenceRecord(
                    trace_context=trace_context,
                    strategy=strategy,
                    type=EvidenceType.CUSTOM,
                    weight=-20,
                    confidence=0.8,
                    source=EvidenceSource.PROVIDER,
                    metadata={"rating": deal.rating, "sub_type": "RATING"}
                ))
                
        # 6. SELLER Evidence (Placeholder, legacy scraper doesn't extract seller yet)
        # Adding for future proofing
        if hasattr(deal, 'seller') and deal.seller:
            if deal.seller.lower() in ["amazon", "appario retail", "retailnet", "fba"]:
                graph.append(EvidenceRecord(
                    trace_context=trace_context,
                    strategy=strategy,
                    type=EvidenceType.SELLER,
                    weight=20,
                    confidence=0.85,
                    source=EvidenceSource.PROVIDER,
                    metadata={"seller": deal.seller}
                ))
                
        # 7. INVENTORY Evidence (Placeholder)
        # 8. HISTORY Evidence (Placeholder)
        
        return graph
