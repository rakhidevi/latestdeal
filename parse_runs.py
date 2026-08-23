import json

try:
    with open('runs.json', 'r') as f:
        data = json.load(f)
    for run in data.get('workflow_runs', []):
        print(f"{run['id']} - {run['name']} - {run['conclusion']}")
except Exception as e:
    print(f"Error: {e}")
