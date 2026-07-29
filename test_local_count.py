
import sqlite3
conn = sqlite3.connect('backend/database/database.sqlite')
cursor = conn.cursor()
cursor.execute('SELECT count(*) FROM deals')
print('Local DB deals count:', cursor.fetchone()[0])

