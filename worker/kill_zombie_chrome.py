import psutil

target_dir = r"K:\WhatsAppUtility\LatestDeal\worker\browser_profile"
killed = 0

for proc in psutil.process_iter(['pid', 'name', 'cmdline']):
    try:
        if proc.info['name'] and 'chrome.exe' in proc.info['name'].lower():
            cmdline = proc.info['cmdline']
            if cmdline:
                cmd_str = " ".join(cmdline)
                if target_dir in cmd_str:
                    print(f"Killing zombie Playwright chrome.exe (PID: {proc.info['pid']})")
                    proc.kill()
                    killed += 1
    except (psutil.NoSuchProcess, psutil.AccessDenied, psutil.ZombieProcess):
        pass

print(f"Cleanup complete. Killed {killed} zombie processes.")
