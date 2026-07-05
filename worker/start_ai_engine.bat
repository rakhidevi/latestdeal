@echo off
title LatestDeal AI Engine
echo Starting the AI Engine...
echo.

:: Start the main worker in a separate minimized window
start "LatestDeal Worker" /MIN cmd /c "venv\Scripts\python.exe main.py"
echo [SUCCESS] Main Worker is running in the background!
echo.

echo Starting the Deal Hunter Loop...
echo The hunter will scan Amazon for new deals every 60 minutes.
echo Keep this window open to continue hunting!
echo.

:loop
echo [%time%] Hunting for new deals...
venv\Scripts\python.exe hunter.py
echo.
echo [%time%] Hunter finished. Sleeping for 60 minutes...
timeout /t 3600
goto loop
