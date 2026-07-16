import os
import sqlite3
import subprocess
import threading
import time
import json
from flask import Flask, render_template, request, jsonify

app = Flask(__name__)

# Process tracking
processes = {
    'server': None,
    'desktop': None,
    'hunter': None,
    'telegram': None,
    'udemy': None,
    'coursera': None
}

# Real-time logs
logs = {
    'server': [],
    'desktop': [],
    'hunter': [],
    'telegram': [],
    'udemy': [],
    'coursera': []
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
        'telegram': processes['telegram'] is not None and processes['telegram'].poll() is None,
        'udemy': processes['udemy'] is not None and processes['udemy'].poll() is None,
        'coursera': processes['coursera'] is not None and processes['coursera'].poll() is None,
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
        cursor.execute("SELECT id, url, status, type, data, updated_at FROM deals_queue ORDER BY updated_at DESC LIMIT 50")
        rows = cursor.fetchall()
        conn.close()
        
        parsed_rows = []
        for row in rows:
            row_dict = dict(row)
            # Parse JSON data if it exists
            if row_dict.get('data'):
                try:
                    parsed_data = json.loads(row_dict['data'])
                    row_dict['title'] = parsed_data.get('raw_title', '')
                    row_dict['image_url'] = parsed_data.get('image_url', '')
                    row_dict['original_price'] = parsed_data.get('raw_original_price', '')
                    row_dict['discounted_price'] = parsed_data.get('raw_discounted_price', '')
                    row_dict['scraper_type'] = parsed_data.get('scraper_type', '')
                except Exception:
                    pass
            # Remove raw data payload to save bandwidth
            row_dict.pop('data', None)
            parsed_rows.append(row_dict)
            
        return jsonify(parsed_rows)
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/api/control/<action>', methods=['POST'])
def control(action):
    global processes, logs, hunter_settings
    
    data = request.json or {}
    target = data.get('target', 'hunter') # 'server', 'desktop', 'hunter', 'telegram', 'udemy', 'coursera', 'all'
    
    if action == 'start':
        if target in ['server', 'all'] and (processes['server'] is None or processes['server'].poll() is not None):
            logs['server'] = []
            processes['server'] = subprocess.Popen([VENV_PYTHON, 'main.py', '--mode', 'server'], stdout=subprocess.PIPE, stderr=subprocess.STDOUT)
            threading.Thread(target=read_output, args=('server', processes['server'], processes['server'].stdout), daemon=True).start()
            
        if target in ['desktop', 'all'] and (processes['desktop'] is None or processes['desktop'].poll() is not None):
            logs['desktop'] = []
            processes['desktop'] = subprocess.Popen([VENV_PYTHON, 'main.py', '--mode', 'desktop'], stdout=subprocess.PIPE, stderr=subprocess.STDOUT)
            threading.Thread(target=read_output, args=('desktop', processes['desktop'], processes['desktop'].stdout), daemon=True).start()
            
        if target in ['telegram', 'all'] and (processes['telegram'] is None or processes['telegram'].poll() is not None):
            logs['telegram'] = []
            processes['telegram'] = subprocess.Popen([VENV_PYTHON, 'telegram_scraper.py'], stdout=subprocess.PIPE, stderr=subprocess.STDOUT)
            threading.Thread(target=read_output, args=('telegram', processes['telegram'], processes['telegram'].stdout), daemon=True).start()
            
        if target in ['udemy', 'all'] and (processes['udemy'] is None or processes['udemy'].poll() is not None):
            logs['udemy'] = []
            processes['udemy'] = subprocess.Popen([VENV_PYTHON, 'udemy_hunter.py'], stdout=subprocess.PIPE, stderr=subprocess.STDOUT)
            threading.Thread(target=read_output, args=('udemy', processes['udemy'], processes['udemy'].stdout), daemon=True).start()
            
        if target in ['coursera', 'all'] and (processes['coursera'] is None or processes['coursera'].poll() is not None):
            logs['coursera'] = []
            processes['coursera'] = subprocess.Popen([VENV_PYTHON, 'coursera_hunter.py'], stdout=subprocess.PIPE, stderr=subprocess.STDOUT)
            threading.Thread(target=read_output, args=('coursera', processes['coursera'], processes['coursera'].stdout), daemon=True).start()
            
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
        targets_to_stop = ['server', 'desktop', 'hunter', 'telegram', 'udemy', 'coursera'] if target == 'all' else [target]
        for t in targets_to_stop:
            if processes[t]:
                try:
                    processes[t].terminate()
                except Exception as e:
                    print(f"Error stopping {t}: {e}")
            
        return jsonify({'status': 'stopped'})

    elif action == 'hard-restart':
        def restart():
            time.sleep(1)
            subprocess.Popen("START_WORKER.bat", creationflags=subprocess.CREATE_NEW_CONSOLE)
        threading.Thread(target=restart).start()
        return jsonify({'status': 'System is hard restarting. Please refresh the page in 5 seconds.'})

# --- Legacy Daemon Endpoints for Laravel Backend Compatibility ---
@app.route('/status')
def legacy_status():
    is_running = processes['server'] is not None and processes['server'].poll() is None
    return jsonify({
        "running": is_running,
        "logs": logs['server'][-20:] if logs['server'] else []
    })

@app.route('/start', methods=['POST'])
def legacy_start():
    if processes['server'] is None or processes['server'].poll() is not None:
        logs['server'] = []
        processes['server'] = subprocess.Popen([VENV_PYTHON, 'main.py', '--mode', 'server'], stdout=subprocess.PIPE, stderr=subprocess.STDOUT)
        threading.Thread(target=read_output, args=('server', processes['server'], processes['server'].stdout), daemon=True).start()
        msg = "Started"
    else:
        msg = "Already running"
    return jsonify({"status": msg})

@app.route('/stop', methods=['POST'])
def legacy_stop():
    if processes['server']:
        try:
            processes['server'].terminate()
            msg = "Stopped"
        except:
            msg = "Error stopping"
    else:
        msg = "Not running"
    return jsonify({"status": msg})

@app.route('/scrape', methods=['POST'])
def legacy_scrape():
    data = request.json or {}
    url = data.get('url')
    job_type = data.get('type', 'ingestion')
    if url:
        try:
            from database import add_to_queue
            add_to_queue(url, job_type)
            msg = "URL added to queue"
        except Exception as e:
            msg = f"Error: {str(e)}"
    else:
        msg = "Missing URL"
    return jsonify({"status": msg})

@app.route('/hunt', methods=['POST'])
def legacy_hunt():
    data = request.json or {}
    cmd = [VENV_PYTHON, "-u", "hunter.py"]
    if data.get('mode'): cmd.extend(["--mode", str(data['mode'])])
    if data.get('category'): cmd.extend(["--category", str(data['category'])])
    if data.get('brand'): cmd.extend(["--brand", str(data['brand'])])
    if data.get('discount'): cmd.extend(["--discount", str(data['discount'])])
    if data.get('keyword'): cmd.extend(["--keyword", str(data['keyword'])])
    
    try:
        subprocess.Popen(cmd)
        msg = "Custom hunt started in background"
    except Exception as e:
        msg = f"Error: {str(e)}"
    return jsonify({"status": msg})

if __name__ == '__main__':
    app.run(host='127.0.0.1', port=5000, debug=False)
