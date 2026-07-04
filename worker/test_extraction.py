import asyncio
from crawl4ai import LLMConfig
from crawl4ai.extraction_strategy import LLMExtractionStrategy

async def main():
    print("Initializing extraction strategy...")
    extraction_strategy = LLMExtractionStrategy(
        llm_config=LLMConfig(
            provider="ollama/llama3", 
            api_token="no-token",
            base_url="http://localhost:11434"
        ),
        instruction="Extract the product title."
    )
    print("Running arun()...")
    try:
        sections = ["Hello world!"] * 10
        res = await extraction_strategy.arun("http://example.com", sections)
        print("Result:", res)
    except Exception as e:
        print("Error:", type(e), e)

asyncio.run(main())
