from http.server import BaseHTTPRequestHandler, HTTPServer
import subprocess
import json
import threading

scraper_process = None
log_buffer = []

def run_scraper():
    global scraper_process, log_buffer
    log_buffer.clear()
    scraper_process = subprocess.Popen(
        ["python", "-u", "main.py"],
        stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT,
        text=True
    )
    for line in scraper_process.stdout:
        log_buffer.append(line.rstrip())
        if len(log_buffer) > 200:
            log_buffer.pop(0)

class RequestHandler(BaseHTTPRequestHandler):
    def do_POST(self):
        global scraper_process
        if self.path == '/start':
            if scraper_process is None or scraper_process.poll() is not None:
                threading.Thread(target=run_scraper, daemon=True).start()
                msg = "Started"
            else:
                msg = "Already running"
            self.send_response(200)
            self.send_header('Content-Type', 'application/json')
            self.end_headers()
            self.wfile.write(json.dumps({"status": msg}).encode())
        elif self.path == '/stop':
            if scraper_process and scraper_process.poll() is None:
                scraper_process.terminate()
                msg = "Stopped"
            else:
                msg = "Not running"
            self.send_response(200)
            self.send_header('Content-Type', 'application/json')
            self.end_headers()
            self.wfile.write(json.dumps({"status": msg}).encode())
        elif self.path == '/scrape':
            content_length = int(self.headers['Content-Length'])
            post_data = self.rfile.read(content_length)
            try:
                data = json.loads(post_data.decode('utf-8'))
                url = data.get('url')
                if url:
                    from database import add_to_queue
                    add_to_queue(url)
                    msg = "URL added to queue"
                else:
                    msg = "Missing URL"
            except Exception as e:
                msg = f"Error: {str(e)}"
            
            self.send_response(200)
            self.send_header('Content-Type', 'application/json')
            self.end_headers()
            self.wfile.write(json.dumps({"status": msg}).encode())
        else:
            self.send_response(404)
            self.end_headers()

    def do_GET(self):
        global scraper_process, log_buffer
        if self.path == '/status':
            is_running = scraper_process is not None and scraper_process.poll() is None
            self.send_response(200)
            self.send_header('Content-Type', 'application/json')
            self.end_headers()
            self.wfile.write(json.dumps({
                "running": is_running,
                "logs": log_buffer
            }).encode())
        else:
            self.send_response(404)
            self.end_headers()

    def log_message(self, format, *args):
        # Suppress HTTP server logging to console to keep logs clean
        pass

def run():
    server_address = ('', 8001)
    print("Daemon HTTP Server running on port 8001")
    httpd = HTTPServer(server_address, RequestHandler)
    httpd.serve_forever()

if __name__ == '__main__':
    run()
