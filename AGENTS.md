# LatestDeal Architecture & Agent Rules

## 1. System Architecture
- **Backend**: Laravel API running on MilesWeb cPanel (`latestdeal.in`).
- **Scraper**: A heavy-duty local Python worker fleet (`START_WORKER.bat` / `START_NEW_SCRAPERS.bat`) runs on the local Windows machine. 
- **Workflow**: The local Python workers scrape Amazon/Telegram, evaluate the deals using the AI pipeline, and **push** the finished deals to the production Laravel API via HTTP requests. 
- **DO NOT** attempt to run scraping tasks directly on the MilesWeb production server.

## 2. Deployment Constraints
- **Host**: MilesWeb cPanel. 
- **Root Directory**: The live server's web root is `production/public_html/`. The actual Laravel web root is `production/public_html/public/`.
- **OPcache**: The production server aggressively caches PHP bytecode (OPcache) without validating timestamps. Editing a `.php` file on the live server will **not** take effect immediately. You MUST hit an `opcache_reset()` script (like `clear_cache.php`) to force the server to read the new file.
- **Database**: The production server uses `database/database.sqlite`. 
  - **CRITICAL WARNING**: Never deploy a `.zip` file that includes an empty `database.sqlite` over the live environment, or it will overwrite the production database and wipe all live deals.
  - The deployment GitHub Action must EXCLUDE `database/database.sqlite` from `deploy.zip`.

## 3. General Rules
- If deals are missing on production, remember that the local Python worker is responsible for populating them.
- Avoid running destructive commands (`migrate:fresh`) on production unless explicitly rebuilding the entire site from scratch.
