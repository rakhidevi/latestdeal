@echo off
REM ============================================================
REM Auto-start Cloudflare Tunnel for Ollama
REM This exposes your local Ollama (port 11434) to the internet
REM so latestdeal.in production server can use it as AI engine.
REM ============================================================

echo Starting Ollama Cloudflare Tunnel...

REM Path to local cloudflared
set CLOUDFLARED_PATH="k:\WhatsAppUtility\LatestDeal\cloudflared.exe"

REM Check if cloudflared is present
if not exist %CLOUDFLARED_PATH% (
    echo ERROR: cloudflared.exe not found in k:\WhatsAppUtility\LatestDeal
    pause
    exit /b 1
)

REM Check if Ollama is running
curl -s http://localhost:11434/api/tags >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo Ollama not running. Starting Ollama...
    start "" "C:\Users\%USERNAME%\AppData\Local\Programs\Ollama\ollama app.exe"
    timeout /t 5 /nobreak >nul
)

echo.
echo ====================================================
echo  Ollama Tunnel Starting...
echo  The public URL will appear below.
echo  COPY the https://xxx.trycloudflare.com URL and
echo  go to: https://latestdeal.in/setup-ai-keys
echo ====================================================
echo.

REM Start the tunnel - URL will be printed to console
%CLOUDFLARED_PATH% tunnel --url http://localhost:11434
