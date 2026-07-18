import os
import json
from openai import AsyncOpenAI
from pydantic import BaseModel, Field
from dotenv import load_dotenv

load_dotenv()

class ScoreSchema(BaseModel):
    score: int = Field(description="A score from 0-100 indicating how good the deal is")
    reason: str = Field(description="A brief explanation for the score")

async def calculate_value_score(title: str, deal_price: float, results: list) -> int:
    """
    Evaluates prices across competitors and assigns a 0-100 score using Groq/Gemini 
    with a fallback to the local Cloudflare Tunnel.
    """
    if not results:
        return 50
    
    # Primary API (e.g. Groq) and Fallback (Cloudflare Tunnel)
    primary_url = os.getenv('GROQ_API_URL', 'https://api.groq.com/openai/v1')
    primary_key = os.getenv('GROQ_API_KEY', 'dummy_key')
    fallback_url = "https://ai.latestdeal.in/v1"
    
    prompt = f"""
    You are an expert shopping assistant evaluating a deal for "{title}".
    The deal's current price is: {deal_price}
    
    Here are the prices found across competitors:
    {json.dumps(results, indent=2)}
    
    Calculate a Value Score from 0 to 100 based on:
    1. How competitive the deal price is compared to the competitor prices.
    2. Rating and Delivery speed of competitors.
    Return a JSON object matching this schema: {ScoreSchema.model_json_schema()}
    """
    
    # Try Primary
    try:
        client = AsyncOpenAI(base_url=primary_url, api_key=primary_key)
        resp = await client.chat.completions.create(
            model="llama3-8b-8192",
            response_format={"type": "json_object"},
            messages=[
                {"role": "system", "content": "You are a helpful assistant that outputs strictly in JSON format."},
                {"role": "user", "content": prompt}
            ],
            timeout=10
        )
        data = json.loads(resp.choices[0].message.content)
        return int(data.get('score', 75))
    except Exception as e:
        print(f"Primary AI failed: {e}. Falling back to Cloudflare Tunnel.")
        
    # Try Fallback (Local machine via Tunnel)
    try:
        fallback_client = AsyncOpenAI(base_url=fallback_url, api_key="ollama")
        resp = await fallback_client.chat.completions.create(
            model="llama3.1",
            response_format={"type": "json_object"},
            messages=[
                {"role": "system", "content": "You are a helpful assistant that outputs strictly in JSON format."},
                {"role": "user", "content": prompt}
            ],
            timeout=15
        )
        data = json.loads(resp.choices[0].message.content)
        return int(data.get('score', 75))
    except Exception as e:
        print(f"Fallback AI failed: {e}. Returning default score.")
        
    return 75
