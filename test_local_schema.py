
import sqlite3
conn = sqlite3.connect('worker/state.db')
cursor = conn.cursor()
cursor.execute("SELECT sql FROM sqlite_master WHERE type='table' AND name='deals_queue'")
print(cursor.fetchone()[0])

