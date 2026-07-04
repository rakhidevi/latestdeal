import sqlite3
import os

DB_PATH = os.path.join(os.path.dirname(__file__), 'state.db')

def init_db():
    """Initializes the SQLite database for local state management (Crash Resilience)."""
    conn = sqlite3.connect(DB_PATH)
    cursor = conn.cursor()
    
    # Table to track urls that need to be scraped
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS deals_queue (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            url TEXT UNIQUE NOT NULL,
            status TEXT DEFAULT 'pending', -- pending, processing, completed, failed
            retry_count INTEGER DEFAULT 0,
            added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ''')
    
    conn.commit()
    conn.close()

def add_to_queue(url: str):
    conn = sqlite3.connect(DB_PATH)
    cursor = conn.cursor()
    try:
        cursor.execute("INSERT INTO deals_queue (url) VALUES (?)", (url,))
        conn.commit()
    except sqlite3.IntegrityError:
        pass # URL already in queue
    finally:
        conn.close()

def get_next_pending() -> dict:
    conn = sqlite3.connect(DB_PATH)
    conn.row_factory = sqlite3.Row
    cursor = conn.cursor()
    
    cursor.execute("SELECT * FROM deals_queue WHERE status = 'pending' LIMIT 1")
    row = cursor.fetchone()
    
    if row:
        cursor.execute("UPDATE deals_queue SET status = 'processing', updated_at = CURRENT_TIMESTAMP WHERE id = ?", (row['id'],))
        conn.commit()
        conn.close()
        return dict(row)
    
    conn.close()
    return None

def mark_status(item_id: int, status: str):
    conn = sqlite3.connect(DB_PATH)
    cursor = conn.cursor()
    cursor.execute("UPDATE deals_queue SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?", (status, item_id))
    conn.commit()
    conn.close()
