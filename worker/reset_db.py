import sqlite3
import os

db_path = os.path.join(os.path.dirname(__file__), 'state.db')
if os.path.exists(db_path):
    conn = sqlite3.connect(db_path)
    cursor = conn.cursor()
    cursor.execute("UPDATE deals_queue SET status='pending' WHERE status='failed' OR status='processing'")
    conn.commit()
    conn.close()
    print("Reset all failed and stuck processing deals back to pending.")
else:
    print("Database not found.")
