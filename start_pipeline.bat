@echo off
title LatestDeal - Automated Scraper Pipeline
echo ===================================================
echo      Starting LatestDeal Automated Pipeline...
echo ===================================================
echo.
echo [1/2] Starting Publisher Daemon (main.py) in a new background window...
start "LatestDeal Publisher Daemon" cmd /k "cd worker && python main.py --mode server"

echo.
echo Waiting for 3 seconds to let the Daemon initialize...
timeout /t 3 /nobreak >nul

echo.
echo [2/2] Starting Opportunity Engine (live_demo.py)...
cd worker
:LOOP
echo [%time%] Running Scraper Scan...
python new\live_demo.py
echo.
echo ===================================================
echo   Scan Complete. Waiting 15 minutes before next run...
echo ===================================================
timeout /t 900 /nobreak
goto LOOP
