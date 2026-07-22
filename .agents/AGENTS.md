# LatestDeal Workspace Rules

These are strict rules that MUST be followed for all future development in this workspace. Do not deviate from these rules under any circumstances.

## 1. Browser Automation (Playwright) Strict Requirements
- **Real Windows Chrome**: NEVER use the default Playwright Chromium binary. You MUST explicitly use the user's real local Windows Chrome installation: `executable_path=r"C:\Program Files\Google\Chrome\Application\chrome.exe"`.
- **Bot Stealth**: ALWAYS pass `args=["--disable-blink-features=AutomationControlled"]` to prevent detection.
- **Hide Automation Warnings**: ALWAYS pass `ignore_default_args=["--enable-automation", "--no-sandbox"]` when launching persistent contexts to prevent the "unsupported command-line flag" or "automated test software" banners from showing up on the user's screen.
- **Visible Mode**: `headless=False` MUST be used so the user can see the browser and solve CAPTCHAs or login if necessary.

## 2. Playwright Tab Management (Memory Leaks)
- **Do NOT blindy use `new_page()`**: When using a persistent context (`launch_persistent_context`), Playwright automatically creates a default `about:blank` tab. If you call `new_page()` for every scraping task, Chrome will accumulate thousands of blank tabs because it remembers session state.
- **Rule**: ALWAYS re-use the first existing tab instead of creating a new one:
  ```python
  page = context.pages[0] if context.pages else context.new_page()
  ```

## 3. Architecture & APIs
- **No External APIs for Data**: Avoid using 3rd party APIs (like the Impact Radius API) to fetch product/course data if it can be directly scraped via Playwright. Playwright is the preferred source of truth to avoid API rate limits, complex authentication, and dependency on external API uptimes.
- **Local AI over Cloud API (Unless Hosted)**: The system currently uses a local `Ollama` instance at `http://localhost:11434`. Do not change this to OpenAI/Cloud APIs unless explicitly migrating the system to a cloud VPS.

## 4. One-Click Worker Ecosystem
- The entire Python scraping infrastructure (Daemon, Dashboard, Telegram Listener, Amazon Hunter) MUST be launchable via the single `START_WORKER.bat` script.
- Do not introduce new background scripts without adding them to `START_WORKER.bat`. The user expects a single double-click to run everything.

## 5. Strict 100% Verification Rule
- Whenever you complete a task based on a requirement document or implementation plan, you MUST perform a line-by-line reverification of every single requirement, UI element, and technical specification mentioned in the document.
- If ANY part is not 100% implemented, you MUST repeat the implementation and verification process until zero percent is missing. You must not claim a task is complete until this exhaustive check confirms perfection.

## 6. Price Extraction
- MRP - Always displays like M.R.P.: ₹39,990.
- Do not extract unit prices like (₹17,99,000 /100 g) as MRP.

## 7. 3-Tier Git & Deployment Pipeline (Dev ➔ Staging ➔ Production)
- **Step 1 — Local Development (`dev` branch)**: ALL new features, code modifications, and bug fixes MUST be committed and pushed to the `dev` branch (`origin/dev`).
- **Step 2 — Staging Verification (`staging` branch)**: Once feature work on `dev` is complete, merge `dev` into `staging` and push to `origin/staging` to trigger the automated Staging deployment workflow ([.github/workflows/deploy-staging.yml](file:///k:/WhatsAppUtility/LatestDeal/.github/workflows/deploy-staging.yml)). Perform full 100% line-by-line reverification on `staging` environment first.
- **Step 3 — Production Release (`main` branch)**: NEVER push directly to `main` or `origin/main` from local development. Only merge `staging` into `main` and push to `origin/main` when the user explicitly commands "push to production" or "deploy to production".
