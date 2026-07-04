from litellm import completion

try:
    response = completion(
        model="ollama/llama3:latest",
        messages=[{"role": "user", "content": "Hello"}],
        api_base="http://localhost:11434"
    )
    print("Success:", response)
except Exception as e:
    print("Error:", type(e), e)
