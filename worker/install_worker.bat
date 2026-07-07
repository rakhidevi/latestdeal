@echo off
title LatestDeal Automator

echo ========================================================
echo Installing LatestDeal AI Worker as a Background Task
echo ========================================================
echo.
echo This will configure your PC to automatically start the Python AI Worker
echo silently in the background every time you log into Windows.
echo.

schtasks /create /tn "LatestDeal_AI_Worker" /tr "wscript.exe k:\WhatsAppUtility\LatestDeal\worker\hidden_worker.vbs" /sc onlogon /rl highest /f

echo.
echo [SUCCESS] The worker has been automated!
echo.
echo To start it right now without restarting your PC, running...
start wscript.exe k:\WhatsAppUtility\LatestDeal\worker\hidden_worker.vbs
echo Worker started successfully in the background. No console window will appear.
echo.
pause
