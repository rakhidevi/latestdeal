from crawl4ai.extraction_strategy import LLMConfig

print("Before rebuild")
try:
    LLMConfig(provider="ollama/qwen3-coder:latest")
except Exception as e:
    print(f"Error before: {type(e).__name__}: {e}")

try:
    LLMConfig.model_rebuild()
except Exception as e:
    print(f"Error rebuild: {type(e).__name__}: {e}")

try:
    LLMConfig(provider="ollama/qwen3-coder:latest")
    print("Success after rebuild!")
except Exception as e:
    print(f"Error after: {type(e).__name__}: {e}")
