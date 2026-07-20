import time
import json
import sqlite3
from typing import Dict, Any
from database import DB_PATH

def init_metrics_db():
    conn = sqlite3.connect(DB_PATH)
    cursor = conn.cursor()
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS metrics (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            merchant TEXT,
            status TEXT,
            error_type TEXT,
            resolve_time_ms INTEGER,
            scrape_time_ms INTEGER,
            ai_time_ms INTEGER,
            total_time_ms INTEGER,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ''')
    conn.commit()
    conn.close()

class MetricsTracker:
    def __init__(self):
        self.merchant = "unknown"
        self.status = "pending"
        self.error_type = ""
        
        self.t_start = time.time()
        self.t_resolve = 0
        self.t_scrape = 0
        self.t_ai = 0
        
        self._marks: Dict[str, float] = {}

    def mark_start(self, phase: str):
        self._marks[phase] = time.time()

    def mark_end(self, phase: str):
        if phase in self._marks:
            duration_ms = int((time.time() - self._marks[phase]) * 1000)
            if phase == "resolve": self.t_resolve = duration_ms
            elif phase == "scrape": self.t_scrape = duration_ms
            elif phase == "ai": self.t_ai = duration_ms
            
    def record_success(self, merchant: str):
        self.merchant = merchant
        self.status = "success"
        self._save()
        
    def record_failure(self, merchant: str, error_type: str):
        self.merchant = merchant if merchant else "unknown"
        self.status = "failed"
        self.error_type = error_type
        self._save()

    def _save(self):
        total_time_ms = int((time.time() - self.t_start) * 1000)
        try:
            init_metrics_db()
            conn = sqlite3.connect(DB_PATH)
            cursor = conn.cursor()
            cursor.execute("""
                INSERT INTO metrics 
                (merchant, status, error_type, resolve_time_ms, scrape_time_ms, ai_time_ms, total_time_ms)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            """, (self.merchant, self.status, self.error_type, self.t_resolve, self.t_scrape, self.t_ai, total_time_ms))
            conn.commit()
            conn.close()
        except Exception as e:
            print(f"Failed to record metrics: {e}")
            
    @staticmethod
    def print_summary():
        try:
            conn = sqlite3.connect(DB_PATH)
            cursor = conn.cursor()
            cursor.execute("SELECT COUNT(*) FROM metrics")
            total = cursor.fetchone()[0]
            
            cursor.execute("SELECT merchant, COUNT(*) FROM metrics WHERE status='success' GROUP BY merchant")
            successes = dict(cursor.fetchall())
            
            cursor.execute("SELECT AVG(total_time_ms) FROM metrics")
            avg_time = cursor.fetchone()[0] or 0
            
            print("\n📊 --- OBSERVABILITY METRICS ---")
            print(f"Total Deals Processed: {total}")
            print(f"Average Pipeline Time: {int(avg_time)}ms")
            for m, count in successes.items():
                print(f"{m.capitalize()} Successes: {count}")
            print("--------------------------------\n")
            
        except Exception:
            pass
