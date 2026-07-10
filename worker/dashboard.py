import os
import sqlite3
import subprocess
import threading
import time
from flask import Flask, render_template, request, jsonify

app = Flask(__name__)

# Process tracking
processes = {
    'server': None,
    'desktop': None,
    'hunter': None
}

# Real-time logs
logs = {
    'server': [],
    'desktop': [],
    'hunter': []
}

# Settings
hunter_settings = {
    'mode': 'sitestripe_automation',
    'category': '',
    'brand': '',
    'discount': '',
    'keyword': ''
}

DB_PATH = os.path.join(os.path.dirname(__file__), 'state.db')
VENV_PYTHON = os.path.join(os.path.dirname(__file__), 'venv', 'Scripts', 'python.exe')

def read_output(process_name, process, stream):
    for line in iter(stream.readline, b''):
        line_str = line.decode('utf-8', errors='replace').strip()
        if line_str:
            logs[process_name].append(line_str)
            # Keep only last 100 lines
            if len(logs[process_name]) > 100:
                logs[process_name].pop(0)

@app.route('/')
def index():
    return render_template('index.html')

@app.route('/api/status')
def status():
    return jsonify({
        'server': processes['server'] is not None and processes['server'].poll() is None,
        'desktop': processes['desktop'] is not None and processes['desktop'].poll() is None,
        'hunter': processes['hunter'] is not None and processes['hunter'].poll() is None,
        'settings': hunter_settings
    })

@app.route('/api/logs/<process_name>')
def get_logs(process_name):
    if process_name in logs:
        return jsonify({'logs': logs[process_name]})
    return jsonify({'logs': []})

@app.route('/api/queue')
def get_queue():
    try:
        conn = sqlite3.connect(DB_PATH)
        conn.row_factory = sqlite3.Row
        cursor = conn.cursor()
        cursor.execute("SELECT id, url, status, type, updated_at FROM deals_queue ORDER BY updated_at DESC LIMIT 20")
        rows = cursor.fetchall()
        conn.close()
        return jsonify([dict(row) for row in rows])
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/api/control/<action>', methods=['POST'])
def control(action):
    global processes, logs, hunter_settings
    
    data = request.json or {}
    target = data.get('target', 'hunter') # 'server', 'desktop', 'hunter', 'all'
    
    if action == 'start':
        if target in ['server', 'all'] and (processes['server'] is None or processes['server'].poll() is not None):
            logs['server'] = []
            processes['server'] = subprocess.Popen([VENV_PYTHON, 'main.py', '--mode', 'server'], stdout=subprocess.PIPE, stderr=subprocess.STDOUT)
            threading.Thread(target=read_output, args=('server', processes['server'], processes['server'].stdout), daemon=True).start()
            
        if target in ['desktop', 'all'] and (processes['desktop'] is None or processes['desktop'].poll() is not None):
            logs['desktop'] = []
            processes['desktop'] = subprocess.Popen([VENV_PYTHON, 'main.py', '--mode', 'desktop'], stdout=subprocess.PIPE, stderr=subprocess.STDOUT)
            threading.Thread(target=read_output, args=('desktop', processes['desktop'], processes['desktop'].stdout), daemon=True).start()
            
        if target in ['hunter', 'all'] and (processes['hunter'] is None or processes['hunter'].poll() is not None):
            # Update settings if passed
            if 'settings' in data:
                hunter_settings.update(data['settings'])
                
            args = [VENV_PYTHON, 'hunter.py', '--mode', hunter_settings['mode']]
            if hunter_settings['category']: args.extend(['--category', hunter_settings['category']])
            if hunter_settings['brand']: args.extend(['--brand', hunter_settings['brand']])
            if hunter_settings['discount']: args.extend(['--discount', hunter_settings['discount']])
            if hunter_settings['keyword']: args.extend(['--keyword', hunter_settings['keyword']])
            
            logs['hunter'] = []
            processes['hunter'] = subprocess.Popen(args, stdout=subprocess.PIPE, stderr=subprocess.STDOUT)
            threading.Thread(target=read_output, args=('hunter', processes['hunter'], processes['hunter'].stdout), daemon=True).start()
            
        return jsonify({'status': 'started'})
        
    elif action == 'stop':
        if target in ['server', 'all'] and processes['server']:
            processes['server'].terminate()
        if target in ['desktop', 'all'] and processes['desktop']:
            processes['desktop'].terminate()
        if target in ['hunter', 'all'] and processes['hunter']:
            processes['hunter'].terminate()
            
        return jsonify({'status': 'stopped'})

if __name__ == '__main__':
    app.run(host='127.0.0.1', port=5000, debug=False)
