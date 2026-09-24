import os
import json
from openai import OpenAI
from pydantic import BaseModel, Field
from typing import List

from models import Deal, DealCategory, AICategoryFailed
from worker.evidence_builder import EvidenceBuilder

# New Decision Engine Imports
from worker.new.sdk.discovery.decision.engine import OpportunityEngine
from worker.new.sdk.discovery.decision.aggregator import EvidenceAggregator
from worker.new.sdk.foundation.dto.models import TraceContext

class AIEnrichmentSchema(BaseModel):
    category_name: str = Field(description="The canonical category of the product (e.g. Electronics, Fashion)")
    category_confidence: float = Field(description="Confidence score from 0.0 to 1.0 of the categorization")
    caption: str = Field(description="A catchy short caption for this deal")
    summary: str = Field(default="", description="A short 1-2 sentence summary of why this is a good deal")
    verdict: str = Field(default="", description="AI recommendation: Buy Now vs Wait, with a short explanation")
    pros: List[str] = Field(default_factory=list, description="List of 2-3 pros of this product")
    cons: List[str] = Field(default_factory=list, description="List of 1-2 cons of this product")

def enrich_deal(deal: Deal, ollama_url: str = "http://localhost:11434", preserved_score: float = None) -> Deal:
    """Uses deterministic validation engine for scoring, and LLM strictly for content generation."""
    try:
        # 1. Deterministic Scoring Pipeline & Intelligence
        from deal_intelligence import DealIntelligenceEngine
        if not deal.price_intelligence:
            deal.price_intelligence = DealIntelligenceEngine.evaluate(
                current_price=deal.price or 0.0,
                original_price=deal.original_price,
                rating=getattr(deal, "rating", None),
                review_count=getattr(deal, "review_count", None),
                is_prime=getattr(deal, "is_prime", False),
                is_fulfilled=getattr(deal, "is_fulfilled", False)
            )

        v_code = deal.price_intelligence.verdict_code
        h_status = deal.price_intelligence.historical_status
        d_score = deal.price_intelligence.deal_score
        d_qual = deal.price_intelligence.deal_qualification

        if preserved_score is not None:
            deal.ai_score = int(preserved_score)
        else:
            deal.ai_score = d_score

        deal.deal_qualification = d_qual
        deal.verdict_code = v_code
        
        print(f"[Engine] Deterministic Deal Score: {deal.ai_score}/100, Qualification: {d_qual}, Verdict Code: {v_code}")
        
        # 2. LLM Content Generation Pipeline (Strictly Read-Only Verdict)
        import asyncio
        import sys
        sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
        from new.services.ai.ai_router import router
        
        prompt = f"""
        You are an Expert SEO Copywriter and Deal Analyst for LatestDeal. Analyze this product deal:
        Title: {deal.title}
        Brand: {deal.brand or 'Unknown'}
        Price: {deal.price}
        Original Price / MRP: {deal.original_price}
        Merchant: {deal.merchant}

        DETERMINISTIC DEAL INTELLIGENCE:
        - Deal Quality Score: {d_score}/100
        - Historical Status: {h_status}
        - Deal Qualification: {d_qual}
        - REQUIRED VERDICT DIRECTION: {v_code}
        
        If this product information does not look like an actual product deal or looks like an error page, set the category_name strictly to 'nodeal'.
        Otherwise, provide the missing information in strict JSON. Do NOT output the schema itself. Output a JSON object with these exact keys:
        - "category_name" (string)
        - "category_confidence" (float)
        - "caption" (string)
        - "summary" (string)
        - "verdict" (string)
        - "pros" (list of strings)
        - "cons" (list of strings)
        
        CRITICAL INSTRUCTIONS:
        1. 'caption' must be an SEO-optimized, highly engaging title/hook (under 100 chars). Use powerful action words.
        2. 'summary' must be a unique, SEO-friendly meta description (120-150 chars). Include the brand name, product type, and core benefit. Do NOT just repeat the title.
        3. 'verdict' MUST strictly adhere to the REQUIRED VERDICT DIRECTION '{v_code}':
           - If '{v_code}' is 'WAIT': Explain clearly why the shopper should WAIT for a better drop or upcoming sale (e.g. price is near ordinary selling levels and not an exceptional historical low). You are strictly FORBIDDEN from recommending "Buy Now".
           - If '{v_code}' is 'BUY_NOW': Recommend "Buy Now" explaining that the item is at a significant price drop or rare historical low.
           - If '{v_code}' is 'CONSIDER': Explain that this is a fair price or modest discount.
        4. Do NOT include ANY URLs, links, or "Buy Now: https://..." anywhere in your output. We handle linking separately.
        """
        
        response = asyncio.run(router.chat(
            messages=[
                {"role": "system", "content": "You output strictly valid JSON."},
                {"role": "user", "content": prompt}
            ],
            options={"capabilities": ["JSON", "TEXT"], "timeout": 120}
        ))
        
        data = json.loads(response['content'])
        parsed = AIEnrichmentSchema(**data)
        
        deal.category = DealCategory(name=parsed.category_name, confidence=parsed.category_confidence)
        deal.ai_caption = parsed.caption
        deal.verdict = parsed.verdict
        deal.verdict_code = v_code
        deal.deal_qualification = d_qual
        
        # Build Deterministic Trust Metrics
        deal.trust_metrics = {
            "is_prime": getattr(deal, "is_prime", False),
            "is_fulfilled": getattr(deal, "is_fulfilled", False),
            "rating": getattr(deal, "rating", None),
            "review_count": getattr(deal, "review_count", None),
            "lowest_180_days": h_status in ["ALL_TIME_LOW", "LOWEST_90D"],
            "trusted_brand": deal.brand not in [None, "Unknown", "amazon"],
            "bank_offer": "bank" in str(deal.title).lower() or "card" in str(deal.title).lower()
        }
        
        # Calculate Deal Confidence
        conf_score = 50
        reasons = []
        
        if deal.trust_metrics.get("lowest_180_days"):
            conf_score += 20
            reasons.append("Lowest price in 180 days")
        if deal.trust_metrics.get("is_prime") or deal.trust_metrics.get("is_fulfilled"):
            conf_score += 10
            reasons.append("Trusted fulfillment")
        if deal.trust_metrics.get("trusted_brand"):
            conf_score += 10
            reasons.append("Highly rated brand")
        if deal.trust_metrics.get("bank_offer"):
            conf_score += 10
            reasons.append("Extra bank discounts available")
            
        if deal.discount_percent and deal.discount_percent > 30:
            conf_score += 10
            reasons.append(f"Significant {deal.discount_percent}% price drop")
            
        deal.confidence_score = min(conf_score, 100)
        deal.confidence_reasons = reasons
        
        # Optional: We could store summary, pros, and cons in deal.metadata if the Deal model supported it.
        # For now, we just map the legacy fields perfectly.
        
        print(f"[LLM] Content Generated: {parsed.category_name} - {parsed.caption[:30]}...")
        
        return deal
        
    except Exception as e:
        print(f"Enrichment pipeline failed: {e}")
        # Ensure a score exists even on failure
        if not deal.ai_score:
            deal.ai_score = 0
        return deal
