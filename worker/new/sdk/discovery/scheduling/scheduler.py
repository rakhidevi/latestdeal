from typing import List, Dict, Type
from datetime import datetime, timezone, timedelta
from worker.new.sdk.foundation.dto.models import PluginManifestV2, SearchTargetDTO
from worker.new.sdk.foundation.events.bus import EventBus, Event
from worker.new.sdk.foundation.events.types import DiscoveryEvent
from worker.new.sdk.discovery.registry.strategy import StrategyRegistry, BaseDiscoveryStrategy, ExecutionMode
from worker.new.sdk.discovery.scheduling.budget_manager import CrawlBudgetManager
from worker.new.sdk.discovery.scheduling.adaptive import AdaptiveDiscoveryOptimizer

class DiscoveryScheduler:
    """
    Cadence-driven, capability-aware Discovery Orchestrator.
    Replaces the old Profile-driven loop with Strategy-driven loop.
    """
    def __init__(self, budget_manager: CrawlBudgetManager):
        self.budget_manager = budget_manager
        self.last_run_times: Dict[str, datetime] = {}

    def plan_and_dispatch(self, providers: List[PluginManifestV2], total_budget: int = 1000) -> None:
        """
        1. Iterate all registered providers.
        2. Find compatible strategies.
        3. Check cadence.
        4. Allocate budget.
        5. Execute strategy and dispatch to Event Bus / Queue.
        """
        now = datetime.now(timezone.utc)
        
        for provider in providers:
            # Get compatible strategies for this provider
            strategies_classes = StrategyRegistry.get_compatible_strategies(provider)
            if not strategies_classes:
                continue
                
            # Extract metadata
            strategies_metadata = [s.get_metadata() for s in strategies_classes]
            
            # Filter by Cadence (schedule_interval_minutes)
            due_strategies: List[Type[BaseDiscoveryStrategy]] = []
            due_metadata = []
            
            for strategy_class, metadata in zip(strategies_classes, strategies_metadata):
                if metadata.execution_mode in (ExecutionMode.DISABLED, ExecutionMode.PAUSED):
                    continue
                    
                # Dependency DAG Execution check
                deps_satisfied = True
                for dep in metadata.dependencies:
                    dep_key = f"{provider.name}_{dep}"
                    if self.last_run_times.get(dep_key) is None:
                        deps_satisfied = False
                        break
                if not deps_satisfied:
                    continue
                
                strategy_key = f"{provider.name}_{metadata.id}"
                last_run = self.last_run_times.get(strategy_key)
                
                adaptive_interval = AdaptiveDiscoveryOptimizer.get_adaptive_interval(metadata, provider.name)
                
                if not last_run or now >= last_run + timedelta(minutes=adaptive_interval):
                    due_strategies.append(strategy_class)
                    due_metadata.append(metadata)
            
            if not due_strategies:
                continue
                
            # Ask Budget Manager for target allocation limits
            allocations = self.budget_manager.allocate_budget(due_metadata, total_daily_budget=total_budget)
            
            # Execute due strategies
            for strategy_class, metadata in zip(due_strategies, due_metadata):
                budget_limit = allocations.get(metadata.id, 0)
                if budget_limit <= 0:
                    continue
                    
                strategy_key = f"{provider.name}_{metadata.id}"
                print(f"[Scheduler] Executing {metadata.name} on {provider.name} (Budget: {budget_limit})")
                
                try:
                    strategy_instance = strategy_class()
                    targets = strategy_instance.generate_targets(provider, budget_allocation=budget_limit)
                    
                    if metadata.execution_mode == ExecutionMode.SHADOW_ONLY:
                        print(f"[Scheduler] {metadata.name} is in SHADOW mode. Generating targets but tagging as SHADOW.")
                        for t in targets:
                            t.state = "Shadow"
                    
                    # Dispatch generated targets
                    self._dispatch(targets)
                except Exception as e:
                    print(f"[Scheduler] Error executing {metadata.name}: {e}")
                finally:
                    # Update last run time
                    self.last_run_times[strategy_key] = now

    def _dispatch(self, targets: List[SearchTargetDTO]) -> None:
        """Dispatcher: Emits generated targets to the Event Bus to be picked up by the Compatibility Layer."""
        for target in targets:
            # Only set state if not already tagged as Shadow
            if target.state != "Shadow":
                target.state = "Queued"
                
            # Assuming target.trace_context exists; if not, we use target.search_target_uuid
            trace_id = target.trace_context.trace_id if hasattr(target, 'trace_context') else target.search_target_uuid
            EventBus.publish(Event(
                type=DiscoveryEvent.SEARCH_TARGET_GENERATED.value,
                trace_id=trace_id,
                source="DiscoveryScheduler",
                payload={"target": target.model_dump()}
            ))
