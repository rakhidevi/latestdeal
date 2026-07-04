import asyncio
from crawl4ai import AsyncWebCrawler, LLMConfig
from crawl4ai.extraction_strategy import LLMExtractionStrategy
from pydantic import BaseModel, Field

from crawl4ai.async_configs import CrawlerRunConfig

class DealExtractionSchema(BaseModel):
    raw_title: str = Field(description="The product title")

async def main():
    extraction_strategy = LLMExtractionStrategy(
        llm_config=LLMConfig(
            provider="ollama/llama3", 
            api_token="no-token",
            base_url="http://localhost:11434"
        ),
        schema=DealExtractionSchema.model_json_schema(),
        extraction_type="schema",
        instruction="Extract the product title."
    )
    
    config = CrawlerRunConfig(
        extraction_strategy=extraction_strategy,
        cache_mode="bypass"
    )
    
    async with AsyncWebCrawler(verbose=True) as crawler:
        result = await crawler.arun(
            url="https://www.amazon.in/dp/B0BDHX8Z63",
            config=config
        )
        print("Success:", result.success)
        print("Markdown Length:", len(result.markdown) if result.markdown else 0)
        print("Error:", result.error_message)
        print("Extracted Content:", result.extracted_content)

if __name__ == "__main__":
    asyncio.run(main())
