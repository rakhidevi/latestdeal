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
            status TEXT DEFAULT 'pending', -- pending, needs_desktop_processing, ready_for_publish, processing, completed, failed
            type TEXT DEFAULT 'ingestion',
            data TEXT,
            retry_count INTEGER DEFAULT 0,
            added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ''')
    
    try:
        # Add type column if it doesn't exist (for older db migrations)
        cursor.execute("ALTER TABLE deals_queue ADD COLUMN type TEXT DEFAULT 'ingestion'")
    except sqlite3.OperationalError:
        pass # Column already exists
        
    try:
        cursor.execute("ALTER TABLE deals_queue ADD COLUMN data TEXT")
    except sqlite3.OperationalError:
        pass # Column already exists
    
    try:
        cursor.execute("ALTER TABLE deals_queue ADD COLUMN job_uuid TEXT")
    except sqlite3.OperationalError:
        pass
        
    try:
        cursor.execute("ALTER TABLE deals_queue ADD COLUMN provider TEXT")
    except sqlite3.OperationalError:
        pass
        
    try:
        cursor.execute("ALTER TABLE deals_queue ADD COLUMN provider_product_id TEXT")
    except sqlite3.OperationalError:
        pass
        
    try:
        cursor.execute("ALTER TABLE deals_queue ADD COLUMN priority INTEGER DEFAULT 3")
    except sqlite3.OperationalError:
        pass
        
    try:
        cursor.execute("ALTER TABLE deals_queue ADD COLUMN failure_category TEXT")
    except sqlite3.OperationalError:
        pass
    
    conn.commit()
    conn.close()

def add_to_queue(url: str, job_type: str = 'ingestion'):
    init_db() # Ensure schema is up to date
    conn = sqlite3.connect(DB_PATH)
    cursor = conn.cursor()
    try:
        cursor.execute("""
            INSERT INTO deals_queue (url, status, type) VALUES (?, 'pending', ?)
            ON CONFLICT(url) DO UPDATE SET status = 'pending', type = ?
        """, (url, job_type, job_type))
        conn.commit()
    except sqlite3.Error as e:
        print(f"Database error: {e}")
    finally:
        conn.close()

def is_duplicate_discovery(provider: str, provider_product_id: str, strategy: str, profile: str, hours: int = 24) -> bool:
    """Checks if the same discovery job was queued recently."""
    conn = sqlite3.connect(DB_PATH)
    cursor = conn.cursor()
    cursor.execute('''
        SELECT id FROM deals_queue 
        WHERE provider = ? AND provider_product_id = ?
        AND json_extract(data, '$.strategy') = ? 
        AND json_extract(data, '$.discovery_profile') = ?
        AND added_at >= datetime('now', ?)
    ''', (provider, provider_product_id, strategy, profile, f'-{hours} hours'))
    row = cursor.fetchone()
    conn.close()
    return bool(row)

def enqueue_discovery_job(job_dict: dict) -> bool:
    """Enqueues a rich DiscoveryJob DTO."""
    init_db()
    
    provider = job_dict.get('provider', '')
    product_id = job_dict.get('provider_product_id', '')
    strategy = job_dict.get('strategy', '')
    profile = job_dict.get('discovery_profile', '')
    
    if is_duplicate_discovery(provider, product_id, strategy, profile):
        print(f"Duplicate discovery ignored: {provider} {product_id} {strategy}")
        return False
        
    url = job_dict.get('url', '')
    job_uuid = job_dict.get('job_uuid', '')
    deal_type = job_dict.get('deal_type', 'deal')
    priority = 1 if deal_type == 'mega_loot' else 2
    
    import json
    data_json = json.dumps(job_dict)
    
    conn = sqlite3.connect(DB_PATH)
    cursor = conn.cursor()
    try:
        cursor.execute("""
            INSERT INTO deals_queue (url, status, type, job_uuid, provider, provider_product_id, priority, data) 
            VALUES (?, 'pending', ?, ?, ?, ?, ?, ?)
            ON CONFLICT(url) DO UPDATE SET 
                status = 'pending', 
                type = ?, 
                job_uuid = ?,
                provider = ?,
                provider_product_id = ?,
                priority = ?,
                data = ?
        """, (url, deal_type, job_uuid, provider, product_id, priority, data_json,
              deal_type, job_uuid, provider, product_id, priority, data_json))
        conn.commit()
    except sqlite3.Error as e:
        print(f"Database error on enqueue: {e}")
        return False
    finally:
        conn.close()
    return True

def get_next_pending(worker_mode: str = 'server') -> dict:
    conn = sqlite3.connect(DB_PATH)
    conn.row_factory = sqlite3.Row
    cursor = conn.cursor()
    
    if worker_mode == 'server':
        # Server handles new ingestions and publishing results from the desktop worker
        cursor.execute('''
            SELECT * FROM deals_queue 
            WHERE status = 'pending' AND type IN ('mega_loot', 'deal', 'ingestion') 
               OR status = 'ready_for_publish' 
            ORDER BY 
                CASE WHEN status = 'ready_for_publish' THEN 1 ELSE 2 END,
                priority ASC,
                added_at ASC
            LIMIT 1
        ''')
    else:
        # Desktop handles physical browser tasks
        cursor.execute('''
            SELECT * FROM deals_queue 
            WHERE (status = 'pending' AND type = 'sitestripe_automation') 
               OR status = 'needs_desktop_processing' 
            LIMIT 1
        ''')
        
    row = cursor.fetchone()
    
    if row:
        cursor.execute("UPDATE deals_queue SET status = 'processing', updated_at = CURRENT_TIMESTAMP WHERE id = ?", (row['id'],))
        conn.commit()
        conn.close()
        return dict(row)
    
    conn.close()
    return None

def update_job_data(item_id: int, data_json: str, status: str):
    conn = sqlite3.connect(DB_PATH)
    cursor = conn.cursor()
    cursor.execute("UPDATE deals_queue SET data = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?", (data_json, status, item_id))
    conn.commit()
    conn.close()

def mark_status(item_id: int, status: str):
    conn = sqlite3.connect(DB_PATH)
    cursor = conn.cursor()
    cursor.execute("UPDATE deals_queue SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?", (status, item_id))
    conn.commit()
    conn.close()
