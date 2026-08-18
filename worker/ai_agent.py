import json
from typing import List, Optional
from openai import OpenAI
from pydantic import BaseModel, Field, ValidationError

class DealCaptionSchema(BaseModel):
    title: str = Field(description="The short, punchy product title")
    original_price: float = Field(description="The MRP or original price")
    discounted_price: float = Field(description="The final discounted deal price")
    features: List[str] = Field(description="3-4 bullet points highlighting key features with emojis")
    verdict: str = Field(description="1 short sentence on why this is a great deal")
    trust_metrics: str = Field(description="Format the star rating, review count, and brand name nicely (e.g. '⭐️ 4.5/5 (10k+ reviews) | 🏷️ Brand: Apple')")
    promo_code: Optional[str] = Field(default=None, description="The promo or coupon code if one is available")
    tags: List[str] = Field(default_factory=list, description="3-5 relevant tags for the deal (e.g. Electronics, Fashion)")
    ai_score: int = Field(description="Score this deal out of 100 based on price drop, brand value, and features. Be realistic (e.g., 75-99).")

import asyncio
import sys
import os

# Ensure the worker package is in path to import services
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from new.services.ai.ai_router import router

def generate_caption(raw_data: dict, ollama_url: str = "http://localhost:11434") -> dict:
    """
    Passes raw scraped data to AIRouter.
    Forces the model to return strict JSON matching our Pydantic schema.
    """
    prompt = f"""
    You are an expert affiliate marketer. Convert this raw data into a strictly structured JSON object.
    You MUST output valid JSON matching this schema:
    {DealCaptionSchema.model_json_schema()}
    
    Pay special attention to the 'metrics' field to extract the star rating, review count, and brand name to build the trust_metrics string.
    RAW DATA:
    {json.dumps(raw_data)}
    """
    
    print("Attempting AI generation via AIRouter...")
    try:
        # We use the Chat Completions API with JSON mode
        response = asyncio.run(router.chat(
            messages=[
                {"role": "system", "content": "You are a helpful assistant that outputs strictly in JSON format."},
                {"role": "user", "content": prompt}
            ],
            options={"capabilities": ["JSON", "TEXT"]}
        ))
        
        parsed_data = json.loads(response['content'])
        
        # Pydantic Validation (Anti-Hallucination)
        validated_deal = DealCaptionSchema(**parsed_data)
        print(f"Success with {response['provider']} ({response['model']}) via JSON Mode!")
        return validated_deal.model_dump()
        
    except (Exception, ValidationError) as e:
        print(f"AIRouter failed: {str(e)}")
        raise e
