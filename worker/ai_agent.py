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

# The Fallback Chain - utilizing local Ollama models (like qwen2.5-coder or llama3)
MODELS = ["qwen2.5-coder:7b", "llama3.1", "phi3"]

def generate_caption(raw_data: dict, ollama_url: str = "http://localhost:11434") -> dict:
    """
    Passes raw scraped data to Ollama using the OpenAI API format.
    Forces the model to return strict JSON matching our Pydantic schema.
    """
    # Initialize the OpenAI client pointed at local Ollama
    client = OpenAI(
        base_url=f"{ollama_url}/v1",  # Ollama's OpenAI compatibility endpoint
        api_key="ollama" # Dummy key required by SDK
    )
    
    prompt = f"""
    You are an expert affiliate marketer. Convert this raw data into a strictly structured JSON object.
    You MUST output valid JSON matching this schema:
    {DealCaptionSchema.model_json_schema()}
    
    Pay special attention to the 'metrics' field to extract the star rating, review count, and brand name to build the trust_metrics string.
    RAW DATA:
    {json.dumps(raw_data)}
    """
    
    for model in MODELS:
        print(f"Attempting AI generation via OpenAI format with model: {model}")
        try:
            # We use the Chat Completions API with JSON mode
            response = client.chat.completions.create(
                model=model,
                response_format={"type": "json_object"},
                messages=[
                    {"role": "system", "content": "You are a helpful assistant that outputs strictly in JSON format."},
                    {"role": "user", "content": prompt}
                ]
            )
            
            parsed_data = json.loads(response.choices[0].message.content)
            
            # Pydantic Validation (Anti-Hallucination)
            validated_deal = DealCaptionSchema(**parsed_data)
            print(f"Success with {model} via JSON Mode!")
            return validated_deal.model_dump()
            
        except (Exception, ValidationError) as e:
            print(f"Model {model} failed: {str(e)}")
            continue # Try next model in chain
            
    raise Exception("All models in the fallback chain failed to generate structured data.")
