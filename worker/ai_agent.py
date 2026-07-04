import json
from typing import List, Optional
from anthropic import Anthropic
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

# The Fallback Chain - utilizing local Ollama models that support tool calling (like qwen2.5-coder or llama3)
MODELS = ["qwen2.5-coder:7b", "llama3.1", "phi3"]

# Define the JSON Schema for Anthropic Tools
caption_tool = {
    "name": "generate_caption_json",
    "description": "Extracts and formats deal data into a structured JSON object for a social media caption.",
    "input_schema": {
        "type": "object",
        "properties": {
            "title": {"type": "string", "description": "The short, punchy product title"},
            "original_price": {"type": "number", "description": "The MRP or original price"},
            "discounted_price": {"type": "number", "description": "The final discounted deal price"},
            "features": {
                "type": "array",
                "items": {"type": "string"},
                "description": "3-4 bullet points highlighting key features with emojis"
            },
            "verdict": {"type": "string", "description": "1 short sentence on why this is a great deal"},
            "trust_metrics": {"type": "string", "description": "Format the star rating, review count, and brand name nicely (e.g. '⭐️ 4.5/5 (10k+ reviews) | 🏷️ Brand: Apple')"},
            "promo_code": {"type": "string", "description": "The promo or coupon code if one is available"},
            "tags": {
                "type": "array",
                "items": {"type": "string"},
                "description": "3-5 relevant tags for the deal (e.g. Electronics, Fashion)"
            }
        },
        "required": ["title", "original_price", "discounted_price", "features", "verdict", "trust_metrics"]
    }
}

def generate_caption(raw_data: dict, ollama_url: str = "http://localhost:11434") -> dict:
    """
    Passes raw scraped data to Ollama using the Anthropic Messages API format.
    Forces the model to use the 'generate_caption_json' tool to return strict JSON.
    """
    # Initialize the Anthropic client pointed at local Ollama
    client = Anthropic(
        base_url=f"{ollama_url}/v1",  # Ollama's OpenAI/Anthropic compatibility endpoint
        api_key="ollama" # Dummy key required by SDK
    )
    
    prompt = f"""
    You are an expert affiliate marketer. Convert this raw data into a strictly structured JSON object.
    Pay special attention to the 'metrics' field to extract the star rating, review count, and brand name to build the trust_metrics string.
    RAW DATA:
    {json.dumps(raw_data)}
    """
    
    for model in MODELS:
        print(f"Attempting AI generation via Anthropic Tool Calls with model: {model}")
        try:
            # We use the Messages API with forced tool choice
            response = client.messages.create(
                model=model,
                max_tokens=1024,
                tools=[caption_tool],
                tool_choice={"type": "tool", "name": "generate_caption_json"},
                messages=[
                    {"role": "user", "content": prompt}
                ]
            )
            
            # The model is forced to call the tool, so we extract the tool's input arguments (which is the JSON we want)
            tool_call = next((block for block in response.content if block.type == 'tool_use'), None)
            
            if not tool_call:
                raise Exception("Model failed to call the required tool.")
                
            parsed_data = tool_call.input
            
            # Pydantic Validation (Anti-Hallucination)
            validated_deal = DealCaptionSchema(**parsed_data)
            print(f"Success with {model} via Tool Calling!")
            return validated_deal.model_dump()
            
        except (Exception, ValidationError) as e:
            print(f"Model {model} failed: {str(e)}")
            continue # Try next model in chain
            
    raise Exception("All models in the fallback chain failed to generate structured data.")
