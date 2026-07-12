@echo off
title LatestDeal Deal Hunter Loop
echo Starting the Deal Hunter Loop...
echo The hunter will scan Amazon for new deals every 60 minutes.
echo Keep this window open or minimized to continue hunting!
echo.

:loop
echo [%time%] Hunting for new deals...
venv\Scripts\python.exe hunter.py
echo.
echo [%time%] Hunter finished. Sleeping for 60 minutes...
timeout /t 3600
goto loop
