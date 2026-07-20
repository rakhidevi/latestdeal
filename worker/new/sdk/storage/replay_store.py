import os
import uuid
import gzip
import shutil

class ReplayStore:
    """Stores raw HTML snapshots for debugging and regression testing without bot triggers."""
    
    @staticmethod
    def save_snapshot(provider: str, url: str, html_content: str):
        try:
            snapshot_id = str(uuid.uuid4())
            filename = f"{provider}_{snapshot_id}.html.gz"
            latest_filename = f"{provider}_latest.html.gz"
            
            path = os.path.join(os.path.dirname(__file__), '..', '..', '..', '..', '..', 'snapshots', provider)
            os.makedirs(path, exist_ok=True)
            
            filepath = os.path.join(path, filename)
            latest_filepath = os.path.join(path, latest_filename)
            
            with gzip.open(filepath, 'wt', encoding='utf-8') as f:
                f.write(f"<!-- URL: {url} -->\n")
                f.write(html_content)
                
            # Keep a 'latest' pointer for DOM change detection
            shutil.copyfile(filepath, latest_filepath)
                
            return snapshot_id
        except Exception as e:
            print(f"Failed to save snapshot: {e}")
            return None
            
    @staticmethod
    def load_snapshot(provider: str, snapshot_id: str):
        try:
            filename = f"{provider}_{snapshot_id}.html.gz"
            filepath = os.path.join(os.path.dirname(__file__), '..', '..', '..', '..', '..', 'snapshots', provider, filename)
            with gzip.open(filepath, 'rt', encoding='utf-8') as f:
                return f.read()
        except Exception:
            return None
            
    @staticmethod
    def load_latest_snapshot(provider: str):
        try:
            filename = f"{provider}_latest.html.gz"
            filepath = os.path.join(os.path.dirname(__file__), '..', '..', '..', '..', '..', 'snapshots', provider, filename)
            if not os.path.exists(filepath): return None
            
            with gzip.open(filepath, 'rt', encoding='utf-8') as f:
                return f.read()
        except Exception:
            return None
