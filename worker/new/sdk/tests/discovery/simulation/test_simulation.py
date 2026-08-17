import unittest
from worker.new.sdk.foundation.dto.models import ShadowDecisionRecord, OpportunityDecisionDTO, DecisionAction, OpportunityScore, EvidenceRecord, SearchTargetDTO, EvidenceType, EvidenceSource
from worker.new.sdk.discovery.simulation.engine import SimulationEngine
from worker.new.sdk.discovery.simulation.metrics import SimulationMetrics
from worker.new.sdk.discovery.simulation.experiment import StrategyExperimentFramework
from worker.new.sdk.discovery.decision.engine import OpportunityEngine
from worker.new.sdk.discovery.decision.aggregator import EvidenceAggregator
from worker.new.sdk.discovery.decision.policy import DecisionPolicyEngine

class TestSimulationEngine(unittest.TestCase):
    def setUp(self):
        self.aggregator = EvidenceAggregator()
        self.opportunity_engine = OpportunityEngine(self.aggregator)
        self.sim_engine = SimulationEngine(self.opportunity_engine)
        
        self.policy_v1 = DecisionPolicyEngine({
            "decision_policy": {
                "publish": {"minimum_score": 85},
                "review": {"minimum_score": 60}
            }
        }, version="v1.0")
        
        self.policy_v2_strict = DecisionPolicyEngine({
            "decision_policy": {
                "publish": {"minimum_score": 95}, # Stricter
                "review": {"minimum_score": 70}
            }
        }, version="v2.0")
        
    def test_simulation_metrics_precision_recall(self):
        results = [
            {"legacy_published": True, "new_decision": "PUBLISH"}, # True Positive
            {"legacy_published": True, "new_decision": "PUBLISH"}, # True Positive
            {"legacy_published": False, "new_decision": "PUBLISH"}, # False Positive
            {"legacy_published": True, "new_decision": "REJECT"}, # False Negative
            {"legacy_published": False, "new_decision": "REJECT"}, # True Negative
        ]
        
        metrics = SimulationMetrics.compute(results)
        
        self.assertEqual(metrics["total_processed"], 5)
        self.assertEqual(metrics["true_positives"], 2)
        self.assertEqual(metrics["false_positives"], 1)
        self.assertEqual(metrics["false_negatives"], 1)
        self.assertEqual(metrics["true_negatives"], 1)
        
        self.assertAlmostEqual(metrics["precision"], 2/3) # 2 TP / (2 TP + 1 FP)
        self.assertAlmostEqual(metrics["recall"], 2/3) # 2 TP / (2 TP + 1 FN)
        
    def test_simulation_engine_replay(self):
        # Create a historical record that scored 90
        evidence = EvidenceRecord(
            trace_id="trc-test",
            strategy="test",
            type=EvidenceType.PRICE,
            weight=100,
            confidence=0.9, # base score will be 90
            source=EvidenceSource.STRATEGY
        )
        score = OpportunityScore(overall=90)
        old_decision = OpportunityDecisionDTO(
            trace_id="trc-test",
            score=score,
            decision=DecisionAction.PUBLISH, # Published under v1 (threshold 85)
            evidence_graph=[evidence],
            explanation="Old publish",
            policy_version="1.0",
            engine_version="1.0"
        )
        record = ShadowDecisionRecord(
            trace_id="trc-test",
            target_id="tgt-test",
            decision=old_decision,
            legacy_published=True
        )
        
        # Replay against STRICT policy v2 (threshold 95)
        # 90 < 95, so it should change from PUBLISH to REVIEW
        result = self.sim_engine.replay_decision(record, self.policy_v2_strict)
        
        self.assertTrue(result["changed"])
        self.assertEqual(result["old_decision"], "PUBLISH")
        self.assertEqual(result["new_decision"], "REVIEW")

    def test_strategy_experiment(self):
        dataset = [{"id": 1}, {"id": 2}]
        
        def strategy_a(data):
            # Always publishes
            score = OpportunityScore(overall=100, confidence=100)
            return OpportunityDecisionDTO(trace_id="x", score=score, decision=DecisionAction.PUBLISH, explanation="", policy_version="", engine_version="")
            
        def strategy_b(data):
            # Publishes half
            score = OpportunityScore(overall=(100 if data["id"] == 1 else 50), confidence=50)
            decision = DecisionAction.PUBLISH if data["id"] == 1 else DecisionAction.REJECT
            return OpportunityDecisionDTO(trace_id="x", score=score, decision=decision, explanation="", policy_version="", engine_version="")
            
        results = StrategyExperimentFramework.compare_strategies(dataset, strategy_a, strategy_b)
        
        self.assertEqual(results["strategy_a"]["published"], 2)
        self.assertEqual(results["strategy_b"]["published"], 1)
        self.assertEqual(results["winner"], "A")

if __name__ == '__main__':
    unittest.main()
