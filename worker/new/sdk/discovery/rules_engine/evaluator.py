from typing import Dict, Any

class RulesEvaluator:
    """
    No-code YAML Rules Engine Evaluator.
    Evaluates constraints (e.g. discount >= 95) against a dataset.
    """
    
    def evaluate(self, rules: Dict[str, Any], data: Dict[str, Any]) -> bool:
        """
        Evaluates a set of rules against the provided data.
        Returns True if all rules pass, False otherwise.
        """
        if not rules:
            return True
            
        for key, conditions in rules.items():
            if key not in data:
                return False
                
            value = data[key]
            
            # Simple condition evaluators
            if 'gte' in conditions and not (value >= conditions['gte']):
                return False
            if 'lte' in conditions and not (value <= conditions['lte']):
                return False
            if 'gt' in conditions and not (value > conditions['gt']):
                return False
            if 'lt' in conditions and not (value < conditions['lt']):
                return False
            if 'eq' in conditions and not (value == conditions['eq']):
                return False
                
        return True
