import sqlite3
import os

db_path = os.path.join('k:\\WhatsAppUtility\\LatestDeal\\worker', 'state.db')
conn = sqlite3.connect(db_path)
cursor = conn.cursor()

cursor.execute("UPDATE deals_queue SET type='sitestripe_automation' WHERE type='ingestion' AND status='pending'")
conn.commit()

print(f"Updated {cursor.rowcount} deals to 'sitestripe_automation'")
conn.close()
