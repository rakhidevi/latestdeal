import urllib.request
import urllib.error
try:
    req = urllib.request.Request("http://127.0.0.1:8000/publisher/dashboard")
    res = urllib.request.urlopen(req)
    print("STATUS:", res.status)
except urllib.error.HTTPError as e:
    print("HTTP ERROR:", e.code)
    with open("error.html", "wb") as f:
        f.write(e.read())
except Exception as e:
    print("ERROR:", e)
