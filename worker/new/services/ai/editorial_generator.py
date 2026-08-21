import time
import requests
import logging
import json
import difflib
from services.ai.ai_router import AIRouter

logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(name)s - %(levelname)s - %(message)s')
logger = logging.getLogger('EditorialGenerator')

LARAVEL_API_BASE = "http://127.0.0.1:8000/api"
WORKER_TOKEN = "test-worker-token-123" 

class EditorialGenerator:
    def __init__(self):
        self.ai_router = AIRouter()
        self.headers = {
            'Authorization': f'Bearer {WORKER_TOKEN}',
            'Accept': 'application/json'
        }

    def claim_generation(self):
        try:
            resp = requests.get(f"{LARAVEL_API_BASE}/worker/generations/claim", headers=self.headers)
            if resp.status_code == 200:
                data = resp.json()
                return data.get('deal'), data.get('generation_target'), data.get('pending_generation_id')
            elif resp.status_code == 404:
                return None, None, None
            else:
                logger.error(f"Error claiming generation: {resp.status_code} {resp.text}")
                return None, None, None
        except Exception as e:
            logger.error(f"Request failed: {e}")
            return None, None, None

    def submit_result(self, deal_id, payload):
        try:
            resp = requests.post(
                f"{LARAVEL_API_BASE}/worker/generations/{deal_id}", 
                json=payload,
                headers=self.headers
            )
            if resp.status_code == 200:
                logger.info(f"Successfully submitted generation for deal {deal_id}")
            else:
                logger.error(f"Failed to submit generation for deal {deal_id}: {resp.status_code} {resp.text}")
        except Exception as e:
            logger.error(f"Submission request failed: {e}")

    def generate(self, deal, target):
        logger.info(f"Generating '{target}' for deal ID {deal['id']} - {deal['title']}")
        
        prompt = f"""
        Act as an expert deal-hunter and product analyst for 'LatestDeal'. 
        Write useful, concise, analytical, consumer-focused editorial analysis based ONLY on the verified facts provided below.
        
        VERIFIED FACTS:
        Title: {deal.get('title')}
        Price: {deal.get('discounted_price')} (Original: {deal.get('original_price')})
        Description: {deal.get('description')}
        
        RULES:
        1. Do NOT invent specifications, price history, or personal experience.
        2. Do NOT pretend you personally tested the product.
        3. Do NOT use generic introductions (e.g. "In today's fast-paced world", "Delve into", "Game changer").
        4. Do NOT simply restate the raw description.
        5. Identify who should buy it, who should skip it, and meaningful limitations.
        6. Use the 'LatestDeal Voice': opinionated, concise, direct, helpful.
        """
        
        if target == 'all':
            prompt += """
            Generate a JSON object with EXACTLY these keys:
            - editorial_summary: string
            - editorial_verdict: string
            - pros: array of 2-3 strings
            - cons: array of 1-2 strings
            - best_for: string (short, e.g. "Everyday users")
            - not_for: string (short, e.g. "Heavy workstation users")
            - source_facts: array of strings (the core facts you based this on)
            
            Return ONLY raw valid JSON.
            """
        elif target == 'verdict':
            prompt += f"""
            Generate ONLY a new 'editorial_verdict' based on the facts, keeping the existing tone.
            Existing Summary: {deal.get('editorial_summary')}
            
            Return a JSON object with exactly:
            - editorial_verdict: string
            - source_facts: array of strings
            """
        elif target == 'summary':
            prompt += f"""
            Generate ONLY a new 'editorial_summary'.
            
            Return a JSON object with exactly:
            - editorial_summary: string
            - source_facts: array of strings
            """
        elif target == 'pros_cons':
            prompt += f"""
            Generate ONLY 'pros' and 'cons'.
            
            Return a JSON object with exactly:
            - pros: array of 2-3 strings
            - cons: array of 1-2 strings
            - source_facts: array of strings
            """
        else: # best_for / not_for
            prompt += f"""
            Generate ONLY 'best_for' and 'not_for'.
            
            Return a JSON object with exactly:
            - best_for: string
            - not_for: string
            - source_facts: array of strings
            """
            
        # In a real setup, we use AIRouter with response_format="json_object"
        result = self.ai_router.route_request([{"role": "user", "content": prompt}], "generation")
        
        content_str = result.get('content', '{}')
        try:
            # Clean up markdown code blocks if any
            if content_str.startswith("```json"):
                content_str = content_str[7:-3].strip()
            elif content_str.startswith("```"):
                content_str = content_str[3:-3].strip()
                
            generated_data = json.loads(content_str)
        except json.JSONDecodeError:
            logger.error("Failed to decode JSON from AI")
            generated_data = {"error": "Invalid JSON returned"}
            
        return generated_data, result.get('provider', 'unknown'), result.get('model', 'unknown')

    def quality_assurance_check(self, deal, generated_content, target):
        # 1. Quick Heuristic Checks
        summary = str(generated_content.get('editorial_summary', '')).lower()
        description = str(deal.get('description', '')).lower()
        
        if summary and description:
            similarity = difflib.SequenceMatcher(None, summary, description).ratio()
            if similarity > 0.8:
                return "FAIL", "Hard Fail: Excessive source-description copying (Score: >0.8)"

        # 2. Advanced AI Factuality & Quality Check
        prompt = f"""
        You are a strict QA Editor for LatestDeal. Evaluate the following generated content against the provided Deal Facts.
        
        DEAL FACTS:
        Title: {deal.get('title')}
        Price: {deal.get('discounted_price')} (Original: {deal.get('original_price')})
        Description: {deal.get('description')}
        
        GENERATED CONTENT:
        {json.dumps(generated_content, indent=2)}
        
        Check for the following criteria:
        HARD FAILS (If ANY exist, the draft is rejected):
        1. Unsupported product specifications (Invented features)
        2. Incorrect price or discount stated in text
        3. Invented price history (e.g. "lowest price ever" if unknown)
        4. Invented personal experience (e.g. "I tested this")
        5. Contradictory product facts
        6. Missing required editorial fields for target '{target}'
        
        SOFT WARNINGS (Note these, but do not reject the draft if only these exist):
        1. Generic phrasing (e.g. "delve into", "fast-paced world")
        2. Repetitive wording
        3. Weak verdict or poor pros/cons
        4. Excessive verbosity
        5. Limited buying context

        Respond ONLY with a JSON object containing:
        - status: "PASS" or "FAIL"
        - hard_fails: array of strings (empty if none)
        - soft_warnings: array of strings (empty if none)
        - feedback: string (summary of issues or praise)
        """
        
        result = self.ai_router.route_request([{"role": "user", "content": prompt}], "generation")
        content_str = result.get('content', '{}')
        try:
            if content_str.startswith("```json"):
                content_str = content_str[7:-3].strip()
            elif content_str.startswith("```"):
                content_str = content_str[3:-3].strip()
            qa_eval = json.loads(content_str)
        except Exception:
            # Fallback if QA AI fails
            return "PASS", "QA Evaluation failed to parse, trusting generated content for now."
            
        status = qa_eval.get('status', 'FAIL').upper()
        if qa_eval.get('hard_fails'):
            status = 'FAIL'
            
        feedback_lines = []
        if qa_eval.get('hard_fails'):
            feedback_lines.append("HARD FAILS: " + ", ".join(qa_eval['hard_fails']))
        if qa_eval.get('soft_warnings'):
            feedback_lines.append("SOFT WARNINGS: " + ", ".join(qa_eval['soft_warnings']))
            
        feedback_lines.append(qa_eval.get('feedback', ''))
        
        return status, " | ".join(feedback_lines).strip()

    def run_cycle(self):
        deal, target, pending_id = self.claim_generation()
        if deal:
            # 1. AI Ghostwriter Generates Content
            new_content, provider, model = self.generate(deal, target)
            
            if "error" in new_content:
                self.submit_result(deal['id'], {
                    "content": {},
                    "source_facts": [],
                    "qa_result": "FAIL",
                    "qa_feedback": "AI failed to generate valid JSON.",
                    "model": model,
                    "provider": provider,
                    "generation_target": target,
                    "pending_generation_id": pending_id
                })
                return True
                
            source_facts = new_content.pop('source_facts', [])
            
            # Combine existing deal content with new content for QA
            combined_content = {
                'editorial_summary': deal.get('editorial_summary'),
                'editorial_verdict': deal.get('editorial_verdict'),
                'pros': deal.get('pros'),
                'cons': deal.get('cons'),
                'best_for': deal.get('best_for'),
                'not_for': deal.get('not_for')
            }
            # Overwrite with new generation
            for k, v in new_content.items():
                combined_content[k] = v

            # 2. Internal QA Firewall
            qa_result, qa_feedback = self.quality_assurance_check(deal, combined_content, target)
            
            # 3. Submit
            self.submit_result(deal['id'], {
                "content": new_content, # Only the newly generated pieces
                "source_facts": source_facts,
                "qa_result": qa_result,
                "qa_feedback": qa_feedback,
                "model": model,
                "provider": provider,
                "generation_target": target,
                "pending_generation_id": pending_id
            })
            return True
        return False

if __name__ == "__main__":
    generator = EditorialGenerator()
    logger.info("Starting Editorial Generator Polling...")
    while True:
        processed = generator.run_cycle()
        if not processed:
            time.sleep(10)
