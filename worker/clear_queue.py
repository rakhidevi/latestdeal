import sqlite3

def clear_queue_except_latest():
    conn = sqlite3.connect('state.db')
    cursor = conn.cursor()
    cursor.execute('DELETE FROM deals_queue WHERE id NOT IN (SELECT id FROM deals_queue ORDER BY id DESC LIMIT 2);')
    conn.commit()
    conn.close()
    print('Cleared queue except latest 2 deals.')

if __name__ == '__main__':
    clear_queue_except_latest()
