import urllib.request
import ssl

ctx = ssl.create_default_context()
ctx.check_hostname = False
ctx.verify_mode = ssl.CERT_NONE

req = urllib.request.Request("https://api.github.com/repos/rakhidevi/latestdeal/actions/runs/32548695110/logs")
req.add_header('User-Agent', 'Mozilla/5.0')
try:
    with urllib.request.urlopen(req, context=ctx) as response:
        with open('logs.zip', 'wb') as f:
            f.write(response.read())
        print("Downloaded logs.zip")
except Exception as e:
    print(f"Error: {e}")
