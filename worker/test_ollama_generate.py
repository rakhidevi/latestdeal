import urllib.request
import json
import time

print("Testing direct Ollama connection...")
data = json.dumps({
    "model": "llama3:latest",
    "prompt": "Say hello in exactly one word.",
    "stream": False
}).encode('utf-8')

req = urllib.request.Request("http://localhost:11434/api/generate", data=data, headers={'Content-Type': 'application/json'})

start = time.time()
try:
    with urllib.request.urlopen(req, timeout=10) as response:
        result = json.loads(response.read().decode('utf-8'))
        print("Success:", result.get("response"))
        print(f"Time taken: {time.time() - start:.2f}s")
except Exception as e:
    print(f"Error after {time.time() - start:.2f}s:", e)
