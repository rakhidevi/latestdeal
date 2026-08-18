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
    
    import sys
    sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
    from new.services.ai.ai_router import router

    try:
        response = await router.chat(
            messages=[
                {"role": "system", "content": "You are a helpful assistant that outputs strictly in JSON format."},
                {"role": "user", "content": prompt}
            ],
            options={"capabilities": ["JSON", "TEXT"], "timeout": 15.0}
        )
        data = json.loads(response['content'])
        return int(data.get('score', 75))
    except Exception as e:
        print(f"AIRouter failed: {e}. Returning default score.")
        return 75
