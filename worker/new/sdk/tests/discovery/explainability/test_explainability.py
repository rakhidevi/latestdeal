import unittest
from worker.new.sdk.foundation.dto.models import OpportunityDecisionDTO, DecisionAction, OpportunityScore, EvidenceRecord, EvidenceType, EvidenceSource
from worker.new.sdk.discovery.explainability.generator import ExplanationGenerator

class TestExplanationGenerator(unittest.TestCase):
    def test_generate_publish_explanation(self):
        evidence1 = EvidenceRecord(
            trace_id="trc-1",
            strategy="mrp_error",
            type=EvidenceType.PRICE,
            weight=100,
            confidence=0.9,
            source=EvidenceSource.STRATEGY
        )
        evidence2 = EvidenceRecord(
            trace_id="trc-1",
            strategy="premium_brand",
            type=EvidenceType.BRAND,
            weight=20,
            confidence=0.9,
            source=EvidenceSource.STRATEGY
        )
        
        score = OpportunityScore(overall=95, trust=20, confidence=85)
        decision = OpportunityDecisionDTO(
            trace_id="trc-1",
            score=score,
            decision=DecisionAction.PUBLISH,
            evidence_graph=[evidence1, evidence2],
            explanation="",
            policy_version="1.0",
            engine_version="1.0"
        )
        
        explanation = ExplanationGenerator.generate(decision)
        
        self.assertIn("published because the overall score (95)", explanation)
        self.assertIn("mrp_error (PRICE)", explanation)
        self.assertIn("premium_brand (BRAND)", explanation)
        self.assertIn("confidence is high (85%)", explanation)
        self.assertNotIn("negative signals", explanation)
        
    def test_generate_mixed_evidence_explanation(self):
        evidence1 = EvidenceRecord(
            trace_id="trc-2",
            strategy="mega_discount",
            type=EvidenceType.PRICE,
            weight=100,
            confidence=0.9,
            source=EvidenceSource.STRATEGY
        )
        evidence2 = EvidenceRecord(
            trace_id="trc-2",
            strategy="bad_seller",
            type=EvidenceType.SELLER,
            weight=-40,
            confidence=0.9,
            source=EvidenceSource.STRATEGY
        )
        
        score = OpportunityScore(overall=60, trust=-40, confidence=45)
        decision = OpportunityDecisionDTO(
            trace_id="trc-2",
            score=score,
            decision=DecisionAction.REVIEW,
            evidence_graph=[evidence1, evidence2],
            explanation="",
            policy_version="1.0",
            engine_version="1.0"
        )
        
        explanation = ExplanationGenerator.generate(decision)
        
        self.assertIn("sent for review because its overall score (60) was borderline", explanation)
        self.assertIn("positive signals from: mega_discount (PRICE)", explanation)
        self.assertIn("negative signals from: bad_seller (SELLER)", explanation)
        self.assertIn("confidence remains low (45%)", explanation)

if __name__ == '__main__':
    unittest.main()
