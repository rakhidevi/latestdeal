$ErrorActionPreference = 'Stop'

Write-Host "Starting Ollama AI Tunnel..." -ForegroundColor Cyan
Write-Host "Please wait while we connect to Cloudflare and update your website automatically."

# Define the log file
$logFile = "$env:TEMP\cloudflared_tunnel.log"
if (Test-Path $logFile) { Remove-Item $logFile }

# Start cloudflared in the background and redirect output to a log file
$process = Start-Process -FilePath "cloudflared" -ArgumentList "tunnel", "--url", "http://localhost:11434" -RedirectStandardError $logFile -PassThru -WindowStyle Hidden

Write-Host "Tunnel started in background (PID: $($process.Id)). Waiting for URL..."

$tunnelUrl = $null
$attempts = 0

# Tail the log file to find the trycloudflare.com URL
while ($null -eq $tunnelUrl -and $attempts -lt 30) {
    Start-Sleep -Seconds 1
    if (Test-Path $logFile) {
        $logs = Get-Content $logFile -Raw
        if ($logs -match "(https://[a-zA-Z0-9-]+\.trycloudflare\.com)") {
            $tunnelUrl = $matches[1]
        }
    }
    $attempts++
}

if ($tunnelUrl) {
    Write-Host "`n✅ Success! Your AI Tunnel URL is: $tunnelUrl" -ForegroundColor Green
    Write-Host "🔄 Automatically updating your website..." -ForegroundColor Yellow
    
    # Auto-update the website using the setup endpoint
    $setupUrl = "https://latestdeal.in/setup-ai-keys?token=temp-setup-123&ollama_url=$tunnelUrl"
    
    try {
        $response = Invoke-RestMethod -Uri $setupUrl -Method Get
        Write-Host "✅ Website successfully updated! Your AI Assistant is now online." -ForegroundColor Green
        Write-Host "`n⚠️ IMPORTANT: Keep this window open! If you close it, the AI will stop working." -ForegroundColor Red
        Write-Host "Press Ctrl+C to stop."
    } catch {
        Write-Host "❌ Failed to update the website automatically. You can do it manually by visiting:" -ForegroundColor Red
        Write-Host $setupUrl
    }
} else {
    Write-Host "❌ Failed to get the tunnel URL. Please check your internet connection or try again." -ForegroundColor Red
    Stop-Process -Id $process.Id -Force -ErrorAction SilentlyContinue
    exit
}

# Keep the script running so the tunnel stays alive
while ($true) {
    Start-Sleep -Seconds 3600
}
