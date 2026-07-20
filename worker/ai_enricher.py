import os
import json
from openai import OpenAI
from pydantic import BaseModel, Field
from models import Deal, DealCategory, AICategoryFailed

class AIEnrichmentSchema(BaseModel):
    category_name: str = Field(description="The canonical category of the product (e.g. Electronics, Fashion)")
    category_confidence: float = Field(description="Confidence score from 0.0 to 1.0 of the categorization")
    brand: str = Field(default="", description="Extracted brand name")
    ai_score: int = Field(description="A score from 1-100 of how good this deal is")
    caption: str = Field(description="A catchy short caption for this deal")

def enrich_deal(deal: Deal, ollama_url: str = "http://localhost:11434") -> Deal:
    """Uses LLM to stateless-ly enrich the scraped deal data."""
    try:
        client = OpenAI(base_url=f"{ollama_url}/v1", api_key="ollama")
        
        prompt = f"""
        You are an AI Deal Assessor. Analyze this product deal:
        Title: {deal.title}
        Price: {deal.price}
        Original Price: {deal.original_price}
        Merchant: {deal.merchant}
        
        If this product information does not look like an actual product deal or looks like an error page, set the category_name strictly to 'nodeal'.
        Otherwise, provide the missing information in strict JSON matching this schema:
        {AIEnrichmentSchema.model_json_schema()}
        """
        
        response = client.chat.completions.create(
            model="llama3.1",
            response_format={"type": "json_object"},
            messages=[
                {"role": "system", "content": "You output strictly valid JSON."},
                {"role": "user", "content": prompt}
            ],
            timeout=15
        )
        
        data = json.loads(response.choices[0].message.content)
        parsed = AIEnrichmentSchema(**data)
        
        deal.category = DealCategory(name=parsed.category_name, confidence=parsed.category_confidence)
        if parsed.brand: deal.brand = parsed.brand
        deal.ai_score = parsed.ai_score
        deal.ai_caption = parsed.caption
        
        return deal
        
    except Exception as e:
        print(f"AI Enrichment failed: {e}")
        # Fallback to rule engine is handled downstream in Laravel, but we can assign empty here
        # or raise AICategoryFailed if we wanted strict handling.
        # We will just log the failure. The Deal object allows None for category.
        return deal
