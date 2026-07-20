import sqlite3
conn = sqlite3.connect('state.db')
conn.execute("UPDATE deals_queue SET status='pending' WHERE status='failed'")
conn.commit()
print("Reset successful")
