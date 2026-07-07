Set WshShell = CreateObject("WScript.Shell")
' Run the Python worker completely hidden
WshShell.Run "cmd /c k:\WhatsAppUtility\LatestDeal\worker\venv\Scripts\python.exe k:\WhatsAppUtility\LatestDeal\worker\main.py", 0, False
