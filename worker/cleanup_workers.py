import os
import psutil

WORKER_SCRIPTS = [
    'main.py',
    'hunter.py',
    'telegram_scraper.py',
    'compare_worker.py',
    'daemon.py',
    'dashboard.py',
    'udemy_hunter.py',
    'coursera_hunter.py'
]

def cleanup(exclude_pid=None):
    current_pid = os.getpid()
    count = 0
    for proc in psutil.process_iter(['pid', 'name', 'cmdline']):
        try:
            pid = proc.info['pid']
            if pid == current_pid or pid == exclude_pid:
                continue
            cmd = proc.info.get('cmdline') or []
            cmd_str = " ".join(cmd).lower()
            if any(script.lower() in cmd_str for script in WORKER_SCRIPTS):
                print(f"Stopping worker process [{pid}]: {cmd_str[:60]}...")
                try:
                    proc.terminate()
                    proc.wait(timeout=3)
                except (psutil.TimeoutExpired, Exception):
                    proc.kill()
                count += 1
        except (psutil.NoSuchProcess, psutil.AccessDenied):
            continue
    print(f"Targeted cleanup completed: stopped {count} LatestDeal worker instances.")

if __name__ == '__main__':
    cleanup()
