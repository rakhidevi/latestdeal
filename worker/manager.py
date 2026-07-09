import os
import subprocess
import sys

def print_header():
    print("\n=============================================")
    print("        LatestDeal - Worker Manager")
    print("=============================================\n")

def run_main_worker():
    print("\n[+] Starting Main Worker (Queue Processor)...")
    print("Press Ctrl+C to stop.")
    try:
        subprocess.run([sys.executable, "main.py"])
    except KeyboardInterrupt:
        print("\n[-] Worker stopped.")

def run_deal_hunter():
    print("\n[+] Starting Custom Deal Hunter...")
    
    category = input("Enter Category (or press Enter for ALL): ").strip()
    brand = input("Enter Brand (or press Enter for ALL): ").strip()
    keyword = input("Enter Keyword (or press Enter for none): ").strip()
    
    print("\nSelect Target Queue Mode:")
    print("1. Standard Ingestion (AI Caption -> DB)")
    print("2. SiteStripe Automation (Real Browser Login -> Shortlink)")
    mode_choice = input("Choice [1]: ").strip()
    
    mode = "sitestripe_automation" if mode_choice == "2" else "ingestion"
    
    cmd = [sys.executable, "hunter.py", "--mode", mode]
    
    if category: cmd.extend(["--category", category])
    if brand: cmd.extend(["--brand", brand])
    if keyword: cmd.extend(["--keyword", keyword])
        
    print(f"\n[+] Executing: {' '.join(cmd)}")
    try:
        subprocess.run(cmd)
        
        print("\n[+] Custom Deal Hunt completed successfully!")
        auto_run = input("Would you like to start the Main Worker now to process these deals? (Y/n): ").strip().lower()
        if auto_run in ['', 'y', 'yes']:
            run_main_worker()
            
    except KeyboardInterrupt:
        print("\n[-] Hunter stopped.")

def install_playwright():
    print("\n[+] Installing Playwright Chrome Browser...")
    try:
        subprocess.run([sys.executable, "-m", "playwright", "install", "chrome"])
        print("[+] Playwright installed successfully!")
    except Exception as e:
        print(f"[-] Error installing playwright: {e}")

def main_menu():
    while True:
        print_header()
        print("1. Run Main Worker (Processes DB Queue)")
        print("2. Run Custom Deal Hunter (Scrape Amazon & Add to Queue)")
        print("3. Install/Fix Playwright Browser")
        print("4. Exit")
        
        choice = input("\nEnter your choice (1-4): ").strip()
        
        if choice == "1":
            run_main_worker()
        elif choice == "2":
            run_deal_hunter()
        elif choice == "3":
            install_playwright()
        elif choice == "4":
            print("Exiting...")
            sys.exit(0)
        else:
            print("Invalid choice. Please try again.")

if __name__ == "__main__":
    # Ensure we are in the worker directory
    os.chdir(os.path.dirname(os.path.abspath(__file__)))
    main_menu()
