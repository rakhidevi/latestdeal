
import sqlite3
conn = sqlite3.connect('backend/database/database.sqlite')
cursor = conn.cursor()
cursor.execute("SELECT COUNT(*) FROM deals WHERE image_path NOT LIKE 'deals/%'")
print(cursor.fetchone())

