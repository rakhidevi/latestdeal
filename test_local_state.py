
import sqlite3

conn = sqlite3.connect('worker/state.db')
cursor = conn.cursor()
cursor.execute("SELECT status, count(*) FROM deals_queue GROUP BY status")
print(cursor.fetchall())

