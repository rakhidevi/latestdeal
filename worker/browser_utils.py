import os
import time
from playwright.sync_api import Playwright, BrowserContext

def get_profile_dir(profile_name: str = "sitestripe") -> str:
    """Returns partitioned user data directory for a specific worker profile."""
    if profile_name == "sitestripe":
        # The authenticated Amazon Associates session is stored in browser_profile
        return os.path.join(os.path.dirname(__file__), 'browser_profile')
    base_dir = os.path.join(os.path.dirname(__file__), 'browser_profiles', profile_name)
    os.makedirs(base_dir, exist_ok=True)
    return base_dir

def setup_browser_persistent(p: Playwright, profile_name: str = "sitestripe") -> BrowserContext:
    """
    Sets up the Playwright persistent browser context following strict AGENTS.md rules.
    Used when cookies/auth/SiteStripe are required.
    Partitions profiles per worker to prevent cross-worker browser lock collisions.
    """
    user_data_dir = get_profile_dir(profile_name)

    launch_args = {
        "user_data_dir": user_data_dir,
        "headless": False, 
        "executable_path": r"C:\Program Files\Google\Chrome\Application\chrome.exe",
        "args": [
            "--disable-blink-features=AutomationControlled",
            "--no-first-run",
            "--no-default-browser-check"
        ],
        "ignore_default_args": ["--enable-automation", "--no-sandbox"],
        "permissions": ["clipboard-read", "clipboard-write"], 
    }
    
    context = p.chromium.launch_persistent_context(**launch_args)
    try:
        context.add_init_script("""
            window.__copied_sitestripe_link = '';
            if (navigator.clipboard) {
                const orig = navigator.clipboard.writeText;
                navigator.clipboard.writeText = function(text) {
                    window.__copied_sitestripe_link = text;
                    return orig ? orig.apply(this, arguments) : Promise.resolve();
                };
            }
        """)
    except Exception:
        pass
    return context

def setup_browser_stateless(p: Playwright):
    """
    Sets up a stateless browser instance following AGENTS.md rules 
    (real Chrome, headless=False, stealth) without persistent profile.
    Returns (browser, context).
    """
    browser = p.chromium.launch(
        headless=False, 
        executable_path=r"C:\Program Files\Google\Chrome\Application\chrome.exe",
        args=["--disable-blink-features=AutomationControlled"],
        ignore_default_args=["--enable-automation", "--no-sandbox"]
    )
    context = browser.new_context(
        user_agent="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
        permissions=["clipboard-read", "clipboard-write"]
    )
    return browser, context

def get_page(context: BrowserContext):
    """
    Follows AGENTS.md rule to re-use the default about:blank tab
    to prevent memory leaks with persistent contexts.
    """
    return context.pages[0] if context.pages else context.new_page()
