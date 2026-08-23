import tkinter as tk
from tkinter import ttk, scrolledtext
import subprocess
import threading
import json

def log_output(text):
    console_text.config(state=tk.NORMAL)
    console_text.insert(tk.END, text)
    console_text.see(tk.END)
    console_text.config(state=tk.DISABLED)

output_buffer = []

def run_in_thread(cmd_list, on_complete=None):
    global output_buffer
    output_buffer = []
    def worker():
        try:
            process = subprocess.Popen(
                cmd_list,
                stdout=subprocess.PIPE,
                stderr=subprocess.STDOUT,
                text=True,
                bufsize=1,
                universal_newlines=True
            )
            for line in process.stdout:
                output_buffer.append(line)
                root.after(0, log_output, line)
            process.wait()
            root.after(0, log_output, f"\nProcess finished with exit code {process.returncode}\n")
        except Exception as e:
            root.after(0, log_output, f"\nError running script: {str(e)}\n")
        finally:
            if on_complete:
                root.after(0, on_complete)
    
    log_output("-" * 50 + "\n")
    log_output(f"Running: {' '.join(cmd_list)}\n")
    log_output("-" * 50 + "\n")
    thread = threading.Thread(target=worker)
    thread.daemon = True
    thread.start()

# --- Tab 1: Discovery ---
def start_hunt(autonomous=False):
    brand = brand_entry.get().strip()
    category = category_entry.get().strip()
    discount = discount_entry.get().strip()
    
    if not all([brand, category, discount]):
        log_output("Please fill in all fields.\n")
        return
        
    start_btn.config(state=tk.DISABLED)
    auto_btn.config(state=tk.DISABLED)
    cmd = ["python", "run_discovery_pipeline.py", "--brand", brand, "--category", category, "--discount", discount]
    
    if fresh_var.get():
        cmd.append("--fresh-dedup")
        
    def on_discovery_complete():
        deal_ids = []
        metrics = None
        for line in output_buffer:
            if "INGESTED_DEAL_IDS:" in line:
                parts = line.split("INGESTED_DEAL_IDS:")
                if len(parts) > 1:
                    deal_ids_str = parts[1].strip()
                    if deal_ids_str:
                        deal_ids = [int(x.strip()) for x in deal_ids_str.split(",") if x.strip()]
            
            # Parse structured JSON logs for metrics
            try:
                if line.startswith('{') and '"metrics":' in line:
                    log_data = json.loads(line.strip())
                    if "metrics" in log_data:
                        metrics = log_data["metrics"]
            except json.JSONDecodeError:
                pass
                
        if metrics:
            discovered = metrics.get('discovered', 0)
            duplicates = metrics.get('duplicates', 0)
            accessories = metrics.get('accessories', 0)
            valid = metrics.get('actual_product_match', 0)
            ingested = metrics.get('ingested_created', 0) + metrics.get('ingested_updated', 0)
            
            log_output("\n\n" + "="*35 + "\n")
            log_output("          HUNT RESULT\n")
            log_output("="*35 + "\n")
            log_output(f"{str(discovered).rjust(4)} Amazon results\n")
            log_output(f"{str(duplicates).rjust(4)} duplicates\n")
            log_output(f"{str(accessories).rjust(4)} accessory/wrong product\n")
            log_output(f"{str(valid).rjust(4)} valid products\n")
            log_output(f"{str(ingested).rjust(4)} deals sent to Laravel\n\n")
        
        if autonomous:
            if deal_ids:
                run_ai_summarize(autonomous=True, deal_ids=deal_ids)
            else:
                if metrics:
                    log_output("AI: NOT RUN\n")
                    log_output("Production: NOT TOUCHED\n\n")
                log_output("No deals were ingested. Skipping AI pipeline.\n")
                start_btn.config(state=tk.NORMAL)
                auto_btn.config(state=tk.NORMAL)
        else:
            start_btn.config(state=tk.NORMAL)
            auto_btn.config(state=tk.NORMAL)
            
    run_in_thread(cmd, on_discovery_complete)

# --- Tab 2: SiteStripe Shortener ---
def start_sitestripe():
    url = url_entry.get().strip()
    if not url:
        log_output("Please enter an Amazon URL.\n")
        return
        
    sitestripe_btn.config(state=tk.DISABLED)
    cmd = ["python", "../sitestripe_importer.py", url]
    run_in_thread(cmd, lambda: sitestripe_btn.config(state=tk.NORMAL))

import os
from dotenv import load_dotenv
import requests

load_dotenv()
LARAVEL_API_URL = os.getenv("LARAVEL_API_URL", "http://localhost:8000")
LARAVEL_API_TOKEN = os.getenv("LARAVEL_API_TOKEN", "test-worker-token-123")

# --- Tab 3: AI Pipeline ---
def run_ai_summarize(autonomous=False, deal_ids=None):
    if not autonomous:
        ai_btn.config(state=tk.DISABLED)
        
    log_output("\n[AUTONOMOUS] Step 2: Triggering Local AI Summarizer...\n")
    
    if not deal_ids:
        cmd = ["docker", "exec", "latestdeal_backend", "php", "artisan", "deals:summarize"]
    else:
        deal_ids_str = ",".join(map(str, deal_ids))
        cmd = ["docker", "exec", "latestdeal_backend", "php", "artisan", "deals:summarize", f"--deal-ids={deal_ids_str}"]
    
    def on_ai_complete():
        if autonomous:
            run_push_to_production()
        else:
            ai_btn.config(state=tk.NORMAL)
            
    run_in_thread(cmd, on_ai_complete)

def run_push_to_production():
    log_output("\n[AUTONOMOUS] Step 3: Pushing IN_REVIEW deals to Production...\n")
    cmd = ["docker", "exec", "latestdeal_backend", "php", "artisan", "deals:push-to-production"]
    
    def on_push_complete():
        start_btn.config(state=tk.NORMAL)
        auto_btn.config(state=tk.NORMAL)
        log_output("\n--- AUTONOMOUS PIPELINE COMPLETE ---\n")
        log_output("\n        OPEN PRODUCTION REVIEW QUEUE        \n")
        log_output("================================================\n")
        refresh_status()
        
    run_in_thread(cmd, on_push_complete)

def run_ai_pilot():
    ai_btn_pilot.config(state=tk.DISABLED)
    deal_id = pilot_entry.get().strip()
    if not deal_id:
        log_output("Please provide a specific deal ID for the pilot run.\n")
        ai_btn_pilot.config(state=tk.NORMAL)
        return
        
    try:
        deal_ids = [int(x.strip()) for x in deal_id.split(",") if x.strip()]
        run_ai_summarize(autonomous=False, deal_ids=deal_ids)
    except ValueError:
        log_output("Invalid deal ID format. Please use comma-separated integers.\n")
    
    ai_btn_pilot.config(state=tk.NORMAL)

# --- Tab 4: Local Status ---
def refresh_status():
    status_btn.config(state=tk.DISABLED)
    log_output("\nFetching local database deal status...\n")
    
    def worker():
        try:
            cmd = [
                "docker", "exec", "latestdeal_backend", "php", "artisan", "tinker", "--execute",
                "echo json_encode(DB::table('deals')->select('production_sync_status', DB::raw('count(*) as count'))->groupBy('production_sync_status')->get());"
            ]
            result = subprocess.run(cmd, capture_output=True, text=True)
            output = result.stdout.strip()
            
            json_str = output[output.find('['):output.rfind(']')+1] if '[' in output else '[]'
            data = json.loads(json_str)
            
            summary = "\n--- Local Deal Sync Status ---\n"
            total = 0
            for row in data:
                status = row.get("production_sync_status") or "UNKNOWN"
                count = row.get("count", 0)
                summary += f"{status.ljust(15)} : {count}\n"
                total += count
            summary += "-" * 26 + "\n"
            summary += f"TOTAL".ljust(15) + f" : {total}\n"
            summary += "-" * 26 + "\n"
            
            root.after(0, log_output, summary)
        except Exception as e:
            root.after(0, log_output, f"\nError fetching status: {str(e)}\n")
        finally:
            root.after(0, lambda: status_btn.config(state=tk.NORMAL))
            
    thread = threading.Thread(target=worker)
    thread.daemon = True
    thread.start()

# --- UI Setup ---
root = tk.Tk()
root.title("LatestDeal Control Panel")
root.geometry("650x650")
root.configure(padx=10, pady=10)

notebook = ttk.Notebook(root)
notebook.pack(fill=tk.X, pady=(0, 10))

# 1. Discovery Tab
tab_discovery = ttk.Frame(notebook)
notebook.add(tab_discovery, text="1. Discover Deals")

ttk.Label(tab_discovery, text="Brand:").grid(row=0, column=0, padx=5, pady=5, sticky=tk.W)
brand_entry = ttk.Entry(tab_discovery, width=30)
brand_entry.insert(0, "Puma")
brand_entry.grid(row=0, column=1, padx=5, pady=5)

ttk.Label(tab_discovery, text="Category:").grid(row=1, column=0, padx=5, pady=5, sticky=tk.W)
category_entry = ttk.Entry(tab_discovery, width=30)
category_entry.insert(0, "Shoes")
category_entry.grid(row=1, column=1, padx=5, pady=5)

ttk.Label(tab_discovery, text="Discount (%):").grid(row=2, column=0, padx=5, pady=5, sticky=tk.W)
discount_entry = ttk.Entry(tab_discovery, width=30)
discount_entry.insert(0, "60")
discount_entry.grid(row=2, column=1, padx=5, pady=5)

fresh_var = tk.BooleanVar(value=True)
fresh_check = ttk.Checkbutton(tab_discovery, text="Fresh Search (Ignore previously found deals)", variable=fresh_var)
fresh_check.grid(row=3, column=0, columnspan=2, pady=5)

start_btn = ttk.Button(tab_discovery, text="Manual Hunt (Stop at DRAFT)", command=start_hunt)
start_btn.grid(row=4, column=0, padx=5, pady=10)

auto_btn = ttk.Button(tab_discovery, text="Autonomous Hunt & Prepare", command=lambda: start_hunt(autonomous=True))
auto_btn.grid(row=4, column=1, padx=5, pady=10)

# 2. SiteStripe Tab
tab_sitestripe = ttk.Frame(notebook)
notebook.add(tab_sitestripe, text="2. SiteStripe (Short URL)")

ttk.Label(tab_sitestripe, text="Amazon URL:").grid(row=0, column=0, padx=5, pady=10, sticky=tk.W)
url_entry = ttk.Entry(tab_sitestripe, width=50)
url_entry.grid(row=0, column=1, padx=5, pady=10)

sitestripe_btn = ttk.Button(tab_sitestripe, text="Extract Short URL & Ingest", command=start_sitestripe)
sitestripe_btn.grid(row=1, column=0, columnspan=2, pady=10)

# 3. AI Pipeline Tab
tab_ai = ttk.Frame(notebook)
notebook.add(tab_ai, text="3. AI Generation")

ttk.Label(tab_ai, text="Run the AI Editorial Generator on all DRAFT deals.").grid(row=0, column=0, columnspan=2, padx=5, pady=5)

ai_btn = ttk.Button(tab_ai, text="Start AI Summarizer (All)", command=run_ai_summarize)
ai_btn.grid(row=1, column=0, columnspan=2, pady=5)

ttk.Separator(tab_ai, orient='horizontal').grid(row=2, column=0, columnspan=2, sticky='ew', pady=10)

ttk.Label(tab_ai, text="Specific Deal ID (Pilot Mode):").grid(row=3, column=0, padx=5, pady=5)
pilot_entry = ttk.Entry(tab_ai, width=15)
pilot_entry.grid(row=3, column=1, padx=5, pady=5, sticky=tk.W)

ai_btn_pilot = ttk.Button(tab_ai, text="Run Pilot Mode", command=run_ai_pilot)
ai_btn_pilot.grid(row=4, column=0, columnspan=2, pady=5)

# 4. Status Tab
tab_status = ttk.Frame(notebook)
notebook.add(tab_status, text="4. Publishing Status")

ttk.Label(tab_status, text="Check how many deals are in DRAFT, IN_REVIEW, or PUBLISHED.").grid(row=0, column=0, padx=5, pady=10)

status_btn = ttk.Button(tab_status, text="Refresh Database Counts", command=refresh_status)
status_btn.grid(row=1, column=0, pady=10)

# Console Output Frame
console_frame = ttk.LabelFrame(root, text="Console Output")
console_frame.pack(fill=tk.BOTH, expand=True)

console_text = scrolledtext.ScrolledText(console_frame, bg="black", fg="lightgreen", font=("Consolas", 10))
console_text.pack(fill=tk.BOTH, expand=True, padx=5, pady=5)
console_text.config(state=tk.DISABLED)

# Welcome message
root.after(100, lambda: log_output("Welcome to LatestDeal Control Panel.\nSelect a tab above to begin.\n\n"))

root.mainloop()
