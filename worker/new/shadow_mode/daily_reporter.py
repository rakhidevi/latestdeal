import json
import glob
from pathlib import Path
from typing import List, Dict, Any

class DailyCertificationReporter:
    """
    Generates the Daily Certification Report by aggregating metrics across
    all signed run reports and checks against Gate 1 & 2 Promotion Criteria.
    """
    def __init__(self, root_dir: str):
        self.root_dir = Path(root_dir)
        self.reports_dir = self.root_dir / "run_reports"
        
    def _load_reports(self) -> List[Dict[str, Any]]:
        reports = []
        pattern = self.reports_dir / "run_report_*.json"
        for file_path in glob.glob(str(pattern)):
            with open(file_path, "r") as f:
                reports.append(json.load(f))
        return reports

    def generate_report(self):
        reports = self._load_reports()
        
        total_runs = len(reports)
        if total_runs == 0:
            print("No runs recorded yet.")
            return

        total_attempted = sum(r["metrics"]["targets_attempted"] for r in reports)
        total_succeeded = sum(r["metrics"]["targets_succeeded"] for r in reports)
        total_captcha = sum(r["metrics"]["captcha_hits"] for r in reports)
        
        extraction_success = (total_succeeded / total_attempted * 100) if total_attempted else 0
        captcha_rate = (total_captcha / total_attempted * 100) if total_attempted else 0
        
        avg_memory = sum(r["metrics"]["memory_peak_mb"] for r in reports) / total_runs
        
        # Aggregate Selector Health
        selector_totals = {"title": 0, "price": 0, "mrp": 0, "image": 0, "rating": 0}
        valid_selector_reports = 0
        for r in reports:
            sh = r["metrics"].get("selector_health")
            if sh and isinstance(sh, dict):
                valid_selector_reports += 1
                for key in selector_totals:
                    selector_totals[key] += sh.get(key, 0)
                    
        if valid_selector_reports > 0:
            for key in selector_totals:
                selector_totals[key] = round(selector_totals[key] / valid_selector_reports, 2)
                
        print("\n" + "="*50)
        print("DAILY CERTIFICATION REPORT")
        print("="*50)
        print(f"Runs: {total_runs}")
        print(f"Success Rate: {round(extraction_success, 2)}%")
        print(f"CAPTCHA Rate: {round(captcha_rate, 2)}%")
        print(f"Average Memory: {round(avg_memory, 2)} MB")
        
        print("\nSelector Health:")
        for k, v in selector_totals.items():
            print(f"  {k.title().ljust(10)} {v}%")
            
        print("\n" + "="*50)
        print("GATE PROMOTION CRITERIA (Objective Thresholds)")
        print("="*50)
        
        vol_pass = total_runs >= 1000
        ext_pass = extraction_success >= 98.0
        
        print(f"[{'PASS' if vol_pass else 'FAIL'}] Volume (>= 1,000 runs): {total_runs}")
        print(f"[{'PASS' if ext_pass else 'FAIL'}] Extraction Success (>= 98%): {round(extraction_success, 2)}%")
        
        if vol_pass and ext_pass:
            print("\nSTATUS: [CERTIFIED]")
        else:
            print("\nSTATUS: [UNDER CERTIFICATION]")

if __name__ == "__main__":
    reporter = DailyCertificationReporter(r"k:\WhatsAppUtility\LatestDeal\worker\new\shadow_mode")
    reporter.generate_report()
