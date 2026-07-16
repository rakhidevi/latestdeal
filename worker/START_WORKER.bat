@echo off
title LatestDeal AI Worker ^& Dashboard
echo ==============================================
echo   Starting LatestDeal Worker + Dashboard UI
echo ==============================================
echo.

echo 1. Stopping any hidden/stuck Python instances...
taskkill /F /IM python.exe >nul 2>&1

echo 2. Starting the background Daemon (Port 8001)...
start "LatestDeal Daemon" /MIN cmd /c "venv\Scripts\python.exe daemon.py"

echo 3. Starting the Dashboard UI (Port 5000)...
start "LatestDeal Dashboard" /MIN cmd /c "venv\Scripts\python.exe dashboard.py"

echo 4. Starting the Telegram Scraper (Listens for incoming Telegram deals)...
start "Telegram Scraper" /MIN cmd /c "venv\Scripts\python.exe telegram_scraper.py"

echo 5. Starting the AI Hunter (Auto-scans Amazon every 60 mins)...
start "AI Hunter" /MIN cmd /c "run_hunter_loop.bat"

echo.
echo [SUCCESS] Everything is running! (Daemon, Dashboard, Telegram Scraper, and Hunter)
echo The browsers will now pop up VISIBLY on your screen so Amazon/Udemy won't block them.
echo Opening the UI dashboard for you...

:: Wait 3 seconds to ensure Flask starts up, then launch the browser
timeout /t 3 >nul
start http://localhost:5000

echo.
pause
