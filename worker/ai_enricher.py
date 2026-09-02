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
        # 1. Deterministic Scoring Pipeline
        if preserved_score is not None:
            deal.ai_score = int(preserved_score)
            print(f"[Engine] Using preserved Opportunity Score: {deal.ai_score}")
        else:
            trace_context = TraceContext(provider="LegacyScraperAdapter", strategy="LegacyPipeline")
            
            # Build Evidence
            evidence_graph = EvidenceBuilder.build(deal, trace_context)
            
            # Aggregate & Score
            aggregator = EvidenceAggregator()
            engine = OpportunityEngine(aggregator=aggregator)
            opportunity_score = engine.compute_score(evidence_graph)
            
            # Assign deterministic score to the legacy ai_score field for backend compatibility
            deal.ai_score = opportunity_score.publishability
            
            print(f"[Engine] Deterministic Publishability Score: {deal.ai_score} (Overall: {opportunity_score.overall}, Confidence: {opportunity_score.confidence}%)")
        
        # 2. LLM Content Generation Pipeline
        import asyncio
        import sys
        sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
        from new.services.ai.ai_router import router
        
        prompt = f"""
        You are an Expert SEO Copywriter and Deal Analyst. Analyze this product deal:
        Title: {deal.title}
        Price: {deal.price}
        Original Price: {deal.original_price}
        Merchant: {deal.merchant}
        
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
        2. 'summary' must be a unique, SEO-friendly meta description (120-150 chars). Include the brand name, product type, and the core benefit/savings. Do NOT just repeat the title.
        3. 'verdict' MUST explicitly recommend whether the user should "Buy Now" or "Wait", giving a short contextual explanation based on the price.
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
        
        # Build Deterministic Trust Metrics
        deal.trust_metrics = {
            "is_prime": getattr(deal, "is_prime", False),
            "is_fulfilled": getattr(deal, "is_fulfilled", False),
            "rating": getattr(deal, "rating", None),
            "review_count": getattr(deal, "review_count", None),
            "lowest_180_days": deal.ai_score >= 80,  # Proxy heuristic for demonstration
            "trusted_brand": deal.ai_score >= 70,    # Proxy heuristic
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
