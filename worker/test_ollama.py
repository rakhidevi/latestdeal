import urllib.request
import json

try:
    response = urllib.request.urlopen("http://localhost:11434/api/tags", timeout=5)
    data = json.loads(response.read())
    print([model['name'] for model in data.get('models', [])])
except Exception as e:
    print(f"Error: {e}")
