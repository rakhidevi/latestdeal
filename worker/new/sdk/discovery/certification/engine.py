from sqlalchemy.orm import Session
from sqlalchemy import func
from datetime import datetime, timedelta

from worker.new.sdk.foundation.database.models import (
    UCDP_SearchTarget, UCDP_EventStore, UCDP_CommerceLedger, UCDP_Telemetry
)

class CertificationEngine:
    """
    Epic 3: Certification Engine.
    Automates the generation of objective, evidence-based certification reports 
    by querying the Universal Commerce Data Platform (UCDP).
    """
    def __init__(self, session: Session):
        self.session = session
        
    def _evaluate_gate_1(self) -> dict:
        """Gate 1: Live Discovery"""
        total_targets = self.session.query(UCDP_SearchTarget).count()
        
        # We query Telemetry for CAPTCHA rates and HTML extraction success
        captcha_events = self.session.query(UCDP_Telemetry).filter_by(metric_name="captcha_detected").count()
        captcha_rate = (captcha_events / total_targets * 100) if total_targets else 0.0
        
        passed = total_targets > 1000 and captcha_rate < 5.0
        return {
            "name": "Gate 1 - Live Discovery",
            "passed": passed,
            "metrics": {
                "total_targets_generated": total_targets,
                "captcha_rate": f"{captcha_rate:.2f}%",
                "target_volume_adequate": total_targets > 1000
            }
        }
        
    def _evaluate_gate_2(self) -> dict:
        """Gate 2: Compatibility"""
        # Event store should show DTO -> Legacy conversions
        compatibility_events = self.session.query(UCDP_EventStore).filter_by(event_type="LegacyCompatibilityPreserved").count()
        passed = compatibility_events > 1000
        return {
            "name": "Gate 2 - Compatibility",
            "passed": passed,
            "metrics": {
                "compatibility_payloads_validated": compatibility_events
            }
        }

    def _evaluate_gate_4(self) -> dict:
        """Gate 4: Legacy Comparison"""
        shadow_decisions = self.session.query(UCDP_EventStore).filter_by(event_type="ShadowDecisionRecorded").all()
        total_shadow = len(shadow_decisions)
        
        better_decisions = sum(1 for e in shadow_decisions if e.payload.get("decision") == "PUBLISH" and not e.payload.get("legacy_published"))
        
        passed = better_decisions > 50
        return {
            "name": "Gate 4 - Legacy Comparison",
            "passed": passed,
            "metrics": {
                "total_comparisons": total_shadow,
                "new_engine_unique_deals": better_decisions
            }
        }
        
    def _evaluate_gate_4_5(self) -> dict:
        """Gate 4.5: Crawl Budget Efficiency"""
        ledgers = self.session.query(UCDP_CommerceLedger).all()
        total_revenue = sum(l.revenue for l in ledgers)
        total_clicks = sum(l.clicks for l in ledgers)
        
        passed = total_revenue > 1000.0
        return {
            "name": "Gate 4.5 - Crawl Budget Efficiency",
            "passed": passed,
            "metrics": {
                "total_affiliate_revenue": f"INR {total_revenue:.2f}",
                "total_clicks_generated": total_clicks
            }
        }
        
    def generate_report(self) -> str:
        gates = [
            self._evaluate_gate_1(),
            self._evaluate_gate_2(),
            self._evaluate_gate_4(),
            self._evaluate_gate_4_5()
        ]
        
        report = ["# Epic 3: Automated Production Certification Report\n"]
        report.append(f"**Generated:** {datetime.utcnow().strftime('%Y-%m-%d %H:%M:%S')} UTC\n")
        
        all_passed = all(g["passed"] for g in gates)
        status_icon = "✅ APPROVED" if all_passed else "❌ REJECTED"
        report.append(f"## Overall Status: {status_icon}\n")
        
        for gate in gates:
            icon = "✅ PASS" if gate["passed"] else "❌ FAIL"
            report.append(f"### {gate['name']} [{icon}]")
            for k, v in gate["metrics"].items():
                report.append(f"- **{k}**: {v}")
            report.append("")
            
        return "\n".join(report)
