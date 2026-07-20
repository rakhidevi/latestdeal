import sqlite3
conn = sqlite3.connect('../backend/database/database.sqlite')
print(conn.execute("SELECT sql FROM sqlite_master WHERE type='table' AND name='deals'").fetchone()[0])
