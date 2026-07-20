import os
from playwright.sync_api import Playwright, BrowserContext

def setup_browser_persistent(p: Playwright) -> BrowserContext:
    """
    Sets up the Playwright persistent browser context following strict AGENTS.md rules.
    Used ONLY when cookies/auth are required (e.g. Amazon SiteStripe).
    """
    user_data_dir = os.path.join(os.path.dirname(__file__), 'browser_profile')
    os.makedirs(user_data_dir, exist_ok=True)
    
    # Aggressively kill any existing Chrome instance using this exact profile to prevent 'Opening in existing browser session' lock issues.
    os.system(f'wmic process where "name=\'chrome.exe\' and commandline like \'%browser_profile%\'" call terminate >nul 2>&1')

    launch_args = {
        "user_data_dir": user_data_dir,
        "headless": False, 
        "executable_path": r"C:\Program Files\Google\Chrome\Application\chrome.exe",
        "args": ["--disable-blink-features=AutomationControlled"],
        "ignore_default_args": ["--enable-automation", "--no-sandbox"],
        "permissions": ["clipboard-read", "clipboard-write"], 
    }
    
    return p.chromium.launch_persistent_context(**launch_args)

def setup_browser_stateless(p: Playwright):
    """
    Sets up a stateless browser instance following AGENTS.md rules 
    (real Chrome, headless=False, stealth) but without a persistent profile 
    to avoid lock conflicts (as requested in 10-point plan).
    Returns (browser, context).
    """
    browser = p.chromium.launch(
        headless=False, 
        executable_path=r"C:\Program Files\Google\Chrome\Application\chrome.exe",
        args=["--disable-blink-features=AutomationControlled"],
        ignore_default_args=["--enable-automation", "--no-sandbox"]
    )
    context = browser.new_context(
        user_agent="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36"
    )
    return browser, context

def get_page(context: BrowserContext):
    """
    Follows AGENTS.md rule to re-use the default about:blank tab
    to prevent memory leaks with persistent contexts.
    """
    return context.pages[0] if context.pages else context.new_page()
