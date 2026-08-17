from typing import List, Dict, Any
from worker.new.sdk.foundation.dto.models import DiscoveryProfileDTO, SearchTargetDTO

class SearchGenerator:
    """
    Translates a DiscoveryProfile into actionable SearchTargets.
    Pipeline: Permutations ➔ Constraints ➔ Budget ➔ Priority ➔ Search Target
    """
    
    def generate(self, profile: DiscoveryProfileDTO, trace_id: str) -> List[SearchTargetDTO]:
        # Step 1: Permutations
        raw_targets = self._generate_permutations(profile)
        
        # Step 2: Constraints
        valid_targets = self._apply_constraints(raw_targets, profile)
        
        # Step 3: Budget
        budgeted_targets = self._apply_budget(valid_targets, profile.budget_pages)
        
        # Step 4: Priority & Mapping
        final_targets = []
        for index, target_params in enumerate(budgeted_targets):
            final_targets.append(
                SearchTargetDTO(
                    trace_id=trace_id,
                    provider=profile.provider,
                    profile=profile.name,
                    strategy=profile.strategy,
                    priority=profile.priority - index, # slightly decay priority for later pages
                    budget=1,
                    url=self._build_placeholder_url(profile.provider, target_params),
                    parameters=target_params
                )
            )
        
        return final_targets

    def _generate_permutations(self, profile: DiscoveryProfileDTO) -> List[Dict[str, Any]]:
        # In a real implementation, this would cross-multiply brands x categories
        # For Phase 3, we just generate a simple list based on the provided inputs
        permutations = []
        brands = profile.brands if profile.brands else ["*"]
        categories = profile.categories if profile.categories else ["*"]
        
        for b in brands:
            for c in categories:
                permutations.append({"brand": b, "category": c})
                
        return permutations

    def _apply_constraints(self, targets: List[Dict[str, Any]], profile: DiscoveryProfileDTO) -> List[Dict[str, Any]]:
        # In reality, filter based on profile constraints
        return targets

    def _apply_budget(self, targets: List[Dict[str, Any]], budget: int) -> List[Dict[str, Any]]:
        return targets[:budget]

    def _build_placeholder_url(self, provider: str, params: Dict[str, Any]) -> str:
        # A real query builder provided by the plugin would handle this. 
        # This is just a placeholder until Phase 4's AmazonProvider is injected.
        return f"urn:{provider}:search?brand={params.get('brand')}&cat={params.get('category')}"
