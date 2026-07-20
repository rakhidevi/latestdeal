import sqlite3
import json

conn = sqlite3.connect('state.db')
cursor = conn.cursor()
cursor.execute("SELECT id, url, status, type FROM deals_queue WHERE status='failed' ORDER BY added_at DESC LIMIT 5")
for row in cursor.fetchall():
    print(row)
conn.close()
