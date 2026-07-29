
import sqlite3
import json
conn = sqlite3.connect('worker/state.db')
cursor = conn.cursor()
cursor.execute("SELECT data FROM deals_queue WHERE status='completed' AND data IS NOT NULL LIMIT 1")
row = cursor.fetchone()
if row and row[0]:
    try:
        print(json.loads(row[0]).keys())
    except:
        print(row[0][:200])
else:
    print('No data')

