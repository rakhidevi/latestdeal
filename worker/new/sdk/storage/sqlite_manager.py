import sqlite3
import os

class SQLiteManager:
    """Fast local state for telemetry and metrics."""
    def __init__(self, db_path=None):
        if not db_path:
            self.db_path = os.path.join(os.path.dirname(__file__), '..', '..', '..', '..', '..', 'state.db')
        else:
            self.db_path = db_path
        self._init_db()
        
    def _init_db(self):
        conn = sqlite3.connect(self.db_path)
        cursor = conn.cursor()
        cursor.execute('''
            CREATE TABLE IF NOT EXISTS telemetry_metrics (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                metric_name TEXT NOT NULL,
                metric_value REAL NOT NULL,
                provider TEXT,
                timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ''')
        conn.commit()
        conn.close()
        
    def record_metric(self, name: str, value: float, provider: str = "system"):
        conn = sqlite3.connect(self.db_path)
        cursor = conn.cursor()
        cursor.execute('''
            INSERT INTO telemetry_metrics (metric_name, metric_value, provider)
            VALUES (?, ?, ?)
        ''', (name, value, provider))
        conn.commit()
        conn.close()
        
    def get_unpushed_metrics(self):
        conn = sqlite3.connect(self.db_path)
        cursor = conn.cursor()
        cursor.execute('SELECT id, metric_name, metric_value, provider, timestamp FROM telemetry_metrics LIMIT 1000')
        rows = cursor.fetchall()
        conn.close()
        return rows
        
    def clear_metrics(self, ids: list):
        if not ids: return
        conn = sqlite3.connect(self.db_path)
        cursor = conn.cursor()
        cursor.execute(f"DELETE FROM telemetry_metrics WHERE id IN ({','.join(['?']*len(ids))})", ids)
        conn.commit()
        conn.close()
