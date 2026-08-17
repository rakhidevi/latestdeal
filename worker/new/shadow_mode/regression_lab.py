import json
import glob
from pathlib import Path
from typing import List, Dict, Any
from playwright.sync_api import sync_playwright

class ProviderRegressionLaboratory:
    """
    Week 2 Operational Fix: Pure Diffing of DOM states.
    Replays saved HTML snapshots through the JS extractor to find broken selectors.
    """
    def __init__(self, root_dir: str):
        self.root_dir = Path(root_dir)
        self.evidence_dir = self.root_dir / "evidence"
        
    def get_latest_evidence_dirs(self, limit: int = 2) -> List[Path]:
        """Returns the most recent evidence directories."""
        dirs = [d for d in self.evidence_dir.iterdir() if d.is_dir()]
        dirs.sort(key=lambda d: d.stat().st_mtime, reverse=True)
        return dirs[:limit]
        
    def _evaluate_html(self, page, html_file: Path) -> Dict[str, Any]:
        """Loads a local HTML file and runs the exact extraction JS."""
        file_url = f"file:///{html_file.resolve().as_posix()}"
        page.goto(file_url, wait_until="domcontentloaded")
        
        item = page.evaluate("""() => {
            const el = document.querySelector('[data-component-type="s-search-result"]') || document.body;
            
            const titleEl = el.querySelector('h2 a span');
            const priceEl = el.querySelector('.a-price-whole');
            const imgEl = el.querySelector('img.s-image');
            const ratingEl = el.querySelector('.a-icon-alt');
            
            return {
                title: titleEl ? titleEl.innerText : null,
                price: priceEl ? parseFloat(priceEl.innerText.replace(/,/g, '')) : null,
                image: imgEl ? imgEl.src : null,
                rating: ratingEl ? ratingEl.innerText : null,
            };
        }""")
        return item

    def run_regression_diff(self):
        print("\n" + "="*50)
        print("PROVIDER REGRESSION LABORATORY")
        print("="*50)
        
        recent_dirs = self.get_latest_evidence_dirs(limit=2)
        if len(recent_dirs) < 2:
            print("Need at least 2 evidence packages (Yesterday vs Today) to perform a diff.")
            return
            
        today_dir = recent_dirs[0]
        yesterday_dir = recent_dirs[1]
        
        today_html = today_dir / "snapshot.html"
        yesterday_html = yesterday_dir / "snapshot.html"
        
        if not today_html.exists() or not yesterday_html.exists():
            print("Missing snapshot.html in evidence directories.")
            return
            
        print(f"Comparing DOM states:")
        print(f"  Yesterday: {yesterday_dir.name}")
        print(f"  Today:     {today_dir.name}")
        
        with sync_playwright() as p:
            browser = p.chromium.launch(headless=True)
            page = browser.new_page()
            
            yesterday_result = self._evaluate_html(page, yesterday_html)
            today_result = self._evaluate_html(page, today_html)
            
            browser.close()
            
        print("\n--- Selector Diff ---")
        regressions_found = 0
        for selector in ["title", "price", "image", "rating"]:
            y_val = "PASS" if yesterday_result.get(selector) else "FAIL"
            t_val = "PASS" if today_result.get(selector) else "FAIL"
            
            status = "[STABLE]"
            if y_val == "PASS" and t_val == "FAIL":
                status = "[REGRESSION DETECTED]"
                regressions_found += 1
            elif y_val == "FAIL" and t_val == "PASS":
                status = "[RECOVERED]"
                
            print(f"{selector.title().ljust(10)}: {y_val} -> {t_val} [{status}]")
            
        print("\n" + "="*50)
        if regressions_found > 0:
            print(f"ALERT: {regressions_found} DOM Regressions Detected! Do not promote to Certified.")
        else:
            print("CONCLUSION: DOM is stable. No broken selectors.")

if __name__ == "__main__":
    lab = ProviderRegressionLaboratory(r"k:\WhatsAppUtility\LatestDeal\worker\new\shadow_mode")
    lab.run_regression_diff()
