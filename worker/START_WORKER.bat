@echo off
title LatestDeal AI Worker ^& Dashboard
:menu
cls
echo ==============================================
echo   LatestDeal Worker Control Panel
echo ==============================================
echo.
echo NOTE: The Core Daemon (Port 8001) is mandatory and will 
echo automatically start alongside any selected worker.
echo.
echo Please select which components you want to start:
echo.
echo   [1] Start ALL Workers (Default)
echo   [2] Start Core Daemon (Port 8001) ONLY
echo   [3] Start Dashboard UI (Port 5000)
echo   [4] Start Telegram Scraper
echo   [5] Start AI Hunter
echo   [6] Start Live Comparison Worker
echo   [7] Start Main Worker (Queue Processor)
echo.
echo   [8] KILL all running Python workers (Cleanup)
echo   [0] Exit
echo.
set /p choice="Enter your choice (0-8): "

if "%choice%"=="1" goto start_all
if "%choice%"=="2" goto start_daemon
if "%choice%"=="3" goto start_dashboard
if "%choice%"=="4" goto start_telegram
if "%choice%"=="5" goto start_hunter
if "%choice%"=="6" goto start_compare
if "%choice%"=="7" goto start_main
if "%choice%"=="8" goto kill_all
if "%choice%"=="0" exit
goto menu

:kill_all
echo.
echo Stopping any hidden/stuck Python instances...
taskkill /F /IM python.exe >nul 2>&1
echo Done!
pause
goto menu

:start_all
echo.
echo Stopping any hidden/stuck Python instances...
taskkill /F /IM python.exe >nul 2>&1
echo Starting the Core Daemon (Port 8001)...
start "LatestDeal Daemon" /MIN cmd /c "venv\Scripts\python.exe daemon.py"
echo Starting the Dashboard UI (Port 5000)...
start "LatestDeal Dashboard" /MIN cmd /c "venv\Scripts\python.exe dashboard.py"
echo Starting the Telegram Scraper...
start "Telegram Scraper" /MIN cmd /c "venv\Scripts\python.exe telegram_scraper.py"
echo Starting the AI Hunter...
start "AI Hunter" /MIN cmd /c "run_hunter_loop.bat"
echo Starting the Live Comparison Worker...
start "Live Comparison Worker" /MIN cmd /c "venv\Scripts\python.exe compare_worker.py"
echo Starting the Main Worker (Queue Processor)...
start "Main Worker" /MIN cmd /c "venv\Scripts\python.exe main.py"
echo.
echo [SUCCESS] Everything is running! (Daemon, Dashboard, Telegram Scraper, Hunter, Comparison Worker, and Main Worker)
echo Opening the UI dashboard for you...
timeout /t 3 >nul
start http://localhost:5000
echo.
pause
goto menu

:start_daemon
echo Starting the Core Daemon (Port 8001)...
start "LatestDeal Daemon" /MIN cmd /c "venv\Scripts\python.exe daemon.py"
pause
goto menu

:start_dashboard
echo Starting the Core Daemon (Port 8001)...
start "LatestDeal Daemon" /MIN cmd /c "venv\Scripts\python.exe daemon.py"
echo Starting the Dashboard UI (Port 5000)...
start "LatestDeal Dashboard" /MIN cmd /c "venv\Scripts\python.exe dashboard.py"
timeout /t 3 >nul
start http://localhost:5000
pause
goto menu

:start_telegram
echo Starting the Core Daemon (Port 8001)...
start "LatestDeal Daemon" /MIN cmd /c "venv\Scripts\python.exe daemon.py"
echo Starting the Telegram Scraper...
start "Telegram Scraper" /MIN cmd /c "venv\Scripts\python.exe telegram_scraper.py"
pause
goto menu

:start_hunter
echo Starting the Core Daemon (Port 8001)...
start "LatestDeal Daemon" /MIN cmd /c "venv\Scripts\python.exe daemon.py"
echo Starting the AI Hunter...
start "AI Hunter" /MIN cmd /c "run_hunter_loop.bat"
pause
goto menu

:start_compare
echo Starting the Core Daemon (Port 8001)...
start "LatestDeal Daemon" /MIN cmd /c "venv\Scripts\python.exe daemon.py"
echo Starting the Live Comparison Worker...
start "Live Comparison Worker" /MIN cmd /c "venv\Scripts\python.exe compare_worker.py"
pause
goto menu

:start_main
echo Starting the Core Daemon (Port 8001)...
start "LatestDeal Daemon" /MIN cmd /c "venv\Scripts\python.exe daemon.py"
echo Starting the Main Worker (Queue Processor)...
start "Main Worker" /MIN cmd /c "venv\Scripts\python.exe main.py"
pause
goto menu
