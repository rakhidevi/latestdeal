import sqlite3
import os
from typing import List, Optional
from datetime import datetime, timezone
from worker.new.sdk.foundation.dto.models import UniversalProductIdentity

class PriceObservation:
    def __init__(self, price: float, mrp: float, coupon_value: float, observed_at: datetime):
        self.price = price
        self.mrp = mrp
        self.coupon_value = coupon_value
        self.observed_at = observed_at
        
class UniversalPriceHistoryService:
    def __init__(self, db_path: Optional[str] = None):
        if not db_path:
            project_root = os.path.dirname(os.path.dirname(os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))))
            db_path = os.path.join(project_root, "worker", "db", "price_history.sqlite")
            
        os.makedirs(os.path.dirname(db_path), exist_ok=True)
        self.db_path = db_path
        self._init_db()
        
    def _init_db(self):
        with sqlite3.connect(self.db_path) as conn:
            conn.execute('''
                CREATE TABLE IF NOT EXISTS price_history (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    provider TEXT NOT NULL,
                    provider_product_id TEXT NOT NULL,
                    brand TEXT,
                    fingerprint TEXT,
                    price REAL NOT NULL,
                    mrp REAL NOT NULL,
                    coupon_value REAL DEFAULT 0,
                    observed_at TIMESTAMP NOT NULL
                )
            ''')
            conn.execute('CREATE INDEX IF NOT EXISTS idx_history_lookup ON price_history(provider, provider_product_id)')
            conn.execute('CREATE INDEX IF NOT EXISTS idx_history_time ON price_history(observed_at)')
            conn.commit()

    def record_price(self, identity: UniversalProductIdentity, price: float, mrp: float, coupon_value: float = 0.0) -> None:
        with sqlite3.connect(self.db_path) as conn:
            conn.execute('''
                INSERT INTO price_history 
                (provider, provider_product_id, brand, fingerprint, price, mrp, coupon_value, observed_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ''', (
                identity.provider, identity.provider_product_id, identity.brand, 
                identity.fingerprint, price, mrp, coupon_value, datetime.now(timezone.utc).isoformat()
            ))
            conn.commit()
            
    def get_history(self, identity: UniversalProductIdentity, limit: int = 100) -> List[PriceObservation]:
        with sqlite3.connect(self.db_path) as conn:
            cursor = conn.execute('''
                SELECT price, mrp, coupon_value, observed_at 
                FROM price_history 
                WHERE provider = ? AND provider_product_id = ?
                ORDER BY observed_at DESC
                LIMIT ?
            ''', (identity.provider, identity.provider_product_id, limit))
            
            # Return in chronological order
            rows = reversed(cursor.fetchall())
            return [
                PriceObservation(
                    price=row[0],
                    mrp=row[1],
                    coupon_value=row[2],
                    observed_at=datetime.fromisoformat(row[3])
                ) for row in rows
            ]
