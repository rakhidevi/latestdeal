import json
import glob
from pathlib import Path
from typing import List, Dict, Any

class TraceService:
    """
    Exposes Shadow Ledger trace logs for the Discovery Studio UI (Sprint 8).
    Provides deep visibility into the exact opportunity lifecycle.
    """
    def __init__(self, shadow_dir: str):
        self.shadow_dir = Path(shadow_dir)
        
    def get_recent_traces(self, limit: int = 50) -> List[Dict[str, Any]]:
        traces = []
        pattern = self.shadow_dir / "shadow_ledger_*.json"
        
        # Load the most recent ledger files
        files = sorted(glob.glob(str(pattern)), reverse=True)
        
        for file_path in files:
            if len(traces) >= limit:
                break
                
            with open(file_path, "r") as f:
                try:
                    records = json.load(f)
                    traces.extend(records)
                except Exception as e:
                    print(f"Failed to load {file_path}: {e}")
                    
        return traces[:limit]

    def get_trace_by_id(self, target_uuid: str) -> Dict[str, Any]:
        pattern = self.shadow_dir / "shadow_ledger_*.json"
        for file_path in glob.glob(str(pattern)):
            with open(file_path, "r") as f:
                records = json.load(f)
                for record in records:
                    if record.get("search_target_uuid") == target_uuid:
                        return record
        return {}

    def get_run_reports(self, limit: int = 10) -> List[Dict[str, Any]]:
        reports = []
        # assuming reports_dir is shadow_dir/../run_reports if initialized with shadow_mode/output, 
        # let's derive it or assume standard structure.
        reports_dir = self.shadow_dir.parent / "run_reports"
        pattern = reports_dir / "run_report_*.json"
        
        files = sorted(glob.glob(str(pattern)), reverse=True)
        for file_path in files[:limit]:
            with open(file_path, "r") as f:
                reports.append(json.load(f))
        return reports

if __name__ == "__main__":
    service = TraceService(r"k:\WhatsAppUtility\LatestDeal\worker\new\shadow_mode\output")
    reports = service.get_run_reports()
    print(f"Loaded {len(reports)} run reports.")
