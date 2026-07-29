import sqlite3
conn = sqlite3.connect('state.db')
cursor = conn.cursor()
cursor.execute('PRAGMA table_info(deals_queue)')
print(cursor.fetchall())
