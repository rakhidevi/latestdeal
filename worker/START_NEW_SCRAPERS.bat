@echo off
setlocal

:: Set the project root path
set PROJECT_ROOT=%~dp0..
cd /d "%PROJECT_ROOT%"

:: Activate virtual environment
if exist "worker\venv\Scripts\activate.bat" (
    call "worker\venv\Scripts\activate.bat"
) else (
    echo [ERROR] Virtual environment not found at worker\venv
    pause
    exit /b 1
)

:: Set PYTHONPATH so absolute imports work
set PYTHONPATH=.

:MENU
cls
echo =======================================================
echo          LATESTDEAL - NEW SCRAPER DASHBOARD
echo =======================================================
echo.
echo Please select an execution mode:
echo.
echo [1] Live Deal Finder (60%%+)
echo     - Actively hunts for 60%%+ DOM-verified standard deals
echo     - Pushes to Laravel backend as 'deal'
echo.
echo [2] Live Mega Loot Finder (85%%+)
echo     - Actively hunts for 85%%+ DOM-verified mega loot
echo     - Pushes to Laravel backend as 'mega_loot'
echo.
echo [3] Continuous Shadow Mode (Validation Sprint 2)
echo     - Runs the deterministic Opportunity Engine in a continuous loop
echo     - Logs health metrics (CAPTCHA hits, memory usage)
echo     - Generates cryptographic "Run Certification Reports"
echo     - DOES NOT push deals to the live backend
echo.
echo [4] Single-Run Shadow Mode (Validation Sprint 1)
echo     - Tests exactly 1 real Amazon target through the new compatibility layer
echo     - Shows exactly how the Opportunity Score is calculated
echo     - DOES NOT push deals to the live backend
echo.
echo [0] Exit
echo.
set /p CHOICE="Enter your choice (0-4): "

if "%CHOICE%"=="1" goto RUN_DEAL
if "%CHOICE%"=="2" goto RUN_MEGA
if "%CHOICE%"=="3" goto RUN_SHADOW_CONT
if "%CHOICE%"=="4" goto RUN_SHADOW_SINGLE
if "%CHOICE%"=="0" exit /b 0

echo Invalid choice, please try again.
pause
goto MENU

:RUN_DEAL
cls
echo Starting Live Deal Finder (60%%+)...
echo Make sure your Laravel backend (php artisan serve) is running!
echo.
python worker\new\live_demo.py
pause
goto MENU

:RUN_MEGA
cls
echo Starting Live Mega Loot Finder (85%%+)...
echo Make sure your Laravel backend (php artisan serve) is running!
echo.
python worker\new\live_demo.py --mega
pause
goto MENU

:RUN_SHADOW_CONT
cls
echo Starting Continuous Shadow Mode...
echo.
python worker\new\shadow_mode\continuous_shadow.py
pause
goto MENU

:RUN_SHADOW_SINGLE
cls
echo Starting Single-Run Shadow Mode...
echo.
python worker\new\shadow_mode\live_runner.py
pause
goto MENU
