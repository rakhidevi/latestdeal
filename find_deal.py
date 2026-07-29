
import sqlite3
import json

conn = sqlite3.connect('backend/database/database.sqlite')
cursor = conn.cursor()
cursor.execute("SELECT id, title, image_path FROM deals")
deals = cursor.fetchall()
wzatco_deals = [d for d in deals if 'wzatco' in d[1].lower()]
print(json.dumps(wzatco_deals))

