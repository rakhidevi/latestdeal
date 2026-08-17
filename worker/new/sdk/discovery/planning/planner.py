from typing import Dict, Any, List
import time
from worker.new.sdk.foundation.dto.models import SearchTargetDTO
from worker.new.sdk.discovery.planning.search_space_builder import SearchSpaceBuilder
from worker.new.sdk.discovery.planning.yield_estimator import YieldEstimator
from worker.new.sdk.discovery.planning.budget_optimizer import BudgetOptimizer
from worker.new.sdk.discovery.planning.priority_engine import PriorityEngine
from worker.new.sdk.discovery.planning.deduplicator import Deduplicator
from worker.new.sdk.discovery.knowledge.database.models import SearchRunRecord

class DiscoveryPlanner:
    """
    Discovery Planning Engine: Planner
    Coordinates the entire intelligent exploration pipeline.
    """
    
    def __init__(
        self,
        search_space_builder: SearchSpaceBuilder,
        yield_estimator: YieldEstimator,
        budget_optimizer: BudgetOptimizer,
        priority_engine: PriorityEngine,
        deduplicator: Deduplicator
    ):
        self.search_space_builder = search_space_builder
        self.yield_estimator = yield_estimator
        self.budget_optimizer = budget_optimizer
        self.priority_engine = priority_engine
        self.deduplicator = deduplicator
        
    def generate_targets(
        self,
        provider: str,
        profile_name: str,
        strategy: str,
        base_priority: int,
        parameters: Dict[str, Any],
        trace_id: str
    ) -> List[SearchTargetDTO]:
        """
        Executes the full planning pipeline, returning prioritized SearchTargetDTOs.
        """
        start_time = time.time()
        run_record = SearchRunRecord(trace_id=trace_id, profile_name=profile_name)
        
        # 1. Build Space (incorporates Capabilities and Constraints)
        raw_space = self.search_space_builder.build_space(provider, parameters)
        run_record.targets_generated = len(raw_space)
        
        # 2. Estimate Yield for each
        scored_space = []
        for perm in raw_space:
            y_est = self.yield_estimator.estimate_yield(perm, profile_name)
            scored_space.append((perm, y_est))
            
        # 3. Deduplicate (only keep novel permutations)
        unique_perms = self.deduplicator.filter_duplicates(provider, strategy, [s[0] for s in scored_space])
        run_record.duplicate_failures = len(raw_space) - len(unique_perms)
        
        targets = []
        for perm in unique_perms:
            # Re-fetch yield (in real app, we'd keep the tuple mapping)
            y_est = self.yield_estimator.estimate_yield(perm, profile_name)
            
            # 4. Budget & Priority
            budget = self.budget_optimizer.allocate(y_est, base_priority, parameters)
            final_priority = self.priority_engine.calculate(base_priority, y_est, perm)
            
            # 5. Explainability
            explanation = f"Yield: {y_est:.2f}, Priority: {final_priority}, Budget: {budget}"
            
            target = SearchTargetDTO(
                trace_id=trace_id,
                provider=provider,
                profile=profile_name,
                strategy=strategy,
                priority=final_priority,
                budget_cost=budget,
                parameters=perm
            )
            targets.append(target)
            
        run_record.targets_queued = len(targets)
        run_record.execution_time_ms = int((time.time() - start_time) * 1000)
        
        # In a full implementation, run_record would be saved to DB here
        
        return targets
