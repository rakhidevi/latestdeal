import sqlite3
import json

db_path = 'database/database.sqlite'

try:
    conn = sqlite3.connect(db_path)
    cursor = conn.cursor()
    
    print("CATEGORIES:")
    cursor.execute("SELECT id, name, slug FROM categories LIMIT 10")
    for row in cursor.fetchall():
        print(row)
        
    print("\nBRANDS:")
    cursor.execute("SELECT DISTINCT brand FROM deals WHERE brand IS NOT NULL LIMIT 10")
    for row in cursor.fetchall():
        print(row)
        
    print("\nTOP DEALS (By Discount):")
    cursor.execute("""
        SELECT id, title, brand, original_price, discounted_price 
        FROM deals 
        WHERE original_price > 0 AND discounted_price > 0
        ORDER BY (original_price - discounted_price) / original_price DESC 
        LIMIT 10
    """)
    for row in cursor.fetchall():
        print(row)
        
    print("\nDEALS WITH PRICE HISTORY:")
    cursor.execute("""
        SELECT d.id, d.title, COUNT(ph.id) as history_count
        FROM deals d
        JOIN price_history ph ON d.id = ph.deal_id
        GROUP BY d.id
        HAVING history_count > 1
        ORDER BY history_count DESC
        LIMIT 10
    """)
    for row in cursor.fetchall():
        print(row)

except Exception as e:
    print(f"Error: {e}")
finally:
    if 'conn' in locals():
        conn.close()
