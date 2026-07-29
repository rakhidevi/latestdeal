
import sqlite3

conn = sqlite3.connect('worker/state.db')
cursor = conn.cursor()
cursor.execute("UPDATE deals_queue SET status='pending' WHERE status='completed' OR status='failed'")
conn.commit()
print('Deals reset to pending:', cursor.rowcount)

