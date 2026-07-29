
import sqlite3

conn = sqlite3.connect('backend/database/database.sqlite')
cursor = conn.cursor()
cursor.execute("SELECT id, title, image_path FROM deals LIMIT 5")
deals = cursor.fetchall()
for d in deals:
    print(d)

