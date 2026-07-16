@echo off
title LatestDeal AI Control Center
echo ==============================================
echo   Starting LatestDeal Control Center (UI)
echo ==============================================
echo.

echo 1. Stopping any hidden/stuck Python instances...
taskkill /F /IM python.exe >nul 2>&1

echo 2. Starting the Dashboard UI (Port 5000)...
start "LatestDeal Dashboard" /MIN cmd /c "venv\Scripts\python.exe dashboard.py"

echo.
echo [SUCCESS] Control Center UI has started!
echo All workers (Server, Telegram, Hunter, Desktop) can now be managed from the web interface.
echo.
echo Opening the UI dashboard for you...

:: Wait 3 seconds to ensure Flask starts up, then launch the browser
timeout /t 3 >nul
start http://localhost:5000

echo.
pause
