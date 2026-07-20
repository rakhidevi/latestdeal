import difflib
from bs4 import BeautifulSoup
from worker.new.sdk.storage.replay_store import ReplayStore
from worker.new.sdk.events.dispatcher import AlertDispatcher, AlertSeverity

class DOMChangeDetector:
    """Detects structural DOM changes to warn about broken parsers."""
    
    @staticmethod
    def _extract_structure(html: str) -> str:
        """Strips all text content, keeping only the sequence of tags and classes."""
        try:
            soup = BeautifulSoup(html, 'html.parser')
            structure = []
            for tag in soup.find_all(True):
                tag_name = tag.name
                classes = " ".join(tag.get("class", []))
                if classes:
                    structure.append(f"{tag_name}.{classes}")
                else:
                    structure.append(tag_name)
            return "\n".join(structure)
        except Exception:
            return ""

    @staticmethod
    def measure_similarity(html1: str, html2: str) -> float:
        struct1 = DOMChangeDetector._extract_structure(html1)
        struct2 = DOMChangeDetector._extract_structure(html2)
        
        if not struct1 or not struct2: return 0.0
        
        matcher = difflib.SequenceMatcher(None, struct1, struct2)
        return matcher.ratio()
        
    @staticmethod
    def detect_and_alert(provider: str, fresh_html: str, threshold: float = 0.70):
        latest_html = ReplayStore.load_latest_snapshot(provider)
        if not latest_html:
            # First run, no baseline to compare against
            return
            
        similarity = DOMChangeDetector.measure_similarity(latest_html, fresh_html)
        
        if similarity < threshold:
            AlertDispatcher.dispatch(
                provider=provider,
                severity=AlertSeverity.WARNING,
                category="DOM_DEGRADED",
                message=f"Massive structural DOM change detected (Similarity: {similarity:.2%}). Parser may break.",
                metadata={"similarity": similarity, "threshold": threshold}
            )
