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

echo.
echo [SUCCESS] Everything is running!
echo The browsers will now pop up VISIBLY on your screen so Amazon won't block them.
echo You can manage your worker at: http://localhost:5000
echo.
pause
