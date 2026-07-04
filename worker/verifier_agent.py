import os
import sys
import json
import requests
import glob

def get_codebase_context():
    """Gathers critical source code into a single string to feed the LLM."""
    context = ""
    
    # 1. Gather Python Worker files
    worker_files = glob.glob(os.path.join("k:\\WhatsAppUtility\\LatestDeal\\worker", "*.py"))
    for file in worker_files:
        if "venv" not in file and "verifier" not in file:
            with open(file, 'r', encoding='utf-8') as f:
                context += f"\n--- {os.path.basename(file)} ---\n"
                context += f.read()[:5000] # Limit to 5k chars per file to save context window
                
    # 2. Gather critical Laravel files
    backend_dirs = [
        "k:\\WhatsAppUtility\\LatestDeal\\backend\\app\\Http\\Controllers",
        "k:\\WhatsAppUtility\\LatestDeal\\backend\\app\\Jobs",
        "k:\\WhatsAppUtility\\LatestDeal\\backend\\app\\Listeners",
        "k:\\WhatsAppUtility\\LatestDeal\\backend\\routes"
    ]
    
    for b_dir in backend_dirs:
        for root, dirs, files in os.walk(b_dir):
            for file in files:
                if file.endswith(".php"):
                    full_path = os.path.join(root, file)
                    with open(full_path, 'r', encoding='utf-8') as f:
                        context += f"\n--- {file} ---\n"
                        context += f.read()[:3000]
                        
    return context[:60000] # Cap total context to ~60k chars to fit typical 8k token windows

def verify_doc(doc_path):
    print(f"Initializing Verifier Agent for {doc_path}...")
    
    try:
        with open(doc_path, 'r', encoding='utf-8') as f:
            doc_content = f.read()
    except Exception as e:
        print(f"Error reading doc: {e}")
        return

    code_context = get_codebase_context()
    
    prompt = f"""
    You are a strict QA Verifier Agent. 
    I will provide you with a Documentation Specification, and the current Codebase implementation.
    
    Your job is to read the Specification, check the Codebase, and tell me EXACTLY what features are MISSING from the code that were requested in the spec.
    Be extremely critical. If the code does not explicitly contain the logic, mark it as missing.
    
    === SPECIFICATION ===
    {doc_content}
    
    === CODEBASE (Truncated) ===
    {code_context}
    
    Output Format:
    - [MISSING]: Feature X
    - [MISSING]: Feature Y
    If everything is perfectly implemented, output "PASS: 100% IMPLEMENTED".
    """
    
    print("Thinking (sending to Ollama)...")
    
    # We use llama3 or whichever default model is available
    payload = {
        "model": "llama3",
        "prompt": prompt,
        "stream": False
    }
    
    try:
        response = requests.post("http://localhost:11434/api/generate", json=payload, timeout=120)
        if response.status_code == 200:
            result = response.json().get('response', '')
            print("\n" + "="*50)
            print(f"VERDICT FOR {os.path.basename(doc_path)}:")
            print("="*50)
            print(result)
            print("="*50 + "\n")
        else:
            print(f"Ollama API Error: {response.text}")
    except Exception as e:
        print(f"Failed to connect to Ollama: {e}")

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Usage: python verifier_agent.py <path_to_doc.md>")
        sys.exit(1)
        
    verify_doc(sys.argv[1])
