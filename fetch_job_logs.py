import urllib.request
import json
import ssl

ctx = ssl.create_default_context()
ctx.check_hostname = False
ctx.verify_mode = ssl.CERT_NONE

req = urllib.request.Request("https://api.github.com/repos/rakhidevi/latestdeal/actions/runs?per_page=3")
req.add_header('User-Agent', 'Mozilla/5.0')
try:
    with urllib.request.urlopen(req, context=ctx) as response:
        data = json.loads(response.read().decode('utf-8'))
        
        for run in data.get('workflow_runs', []):
            if run['name'] == 'Deploy Production':
                jobs_url = run['jobs_url']
                jobs_req = urllib.request.Request(jobs_url)
                jobs_req.add_header('User-Agent', 'Mozilla/5.0')
                with urllib.request.urlopen(jobs_req, context=ctx) as jobs_response:
                    jobs_data = json.loads(jobs_response.read().decode('utf-8'))
                    for job in jobs_data.get('jobs', []):
                        if job['conclusion'] == 'failure':
                            # fetch logs
                            print(f"Fetching logs for job: {job['id']}")
                            log_url = f"https://api.github.com/repos/rakhidevi/latestdeal/actions/jobs/{job['id']}/logs"
                            log_req = urllib.request.Request(log_url)
                            log_req.add_header('User-Agent', 'Mozilla/5.0')
                            try:
                                with urllib.request.urlopen(log_req, context=ctx) as log_res:
                                    print(log_res.read().decode('utf-8')[-2000:])
                            except Exception as le:
                                print(f"Log fetch error: {le}")
except Exception as e:
    print(f"Error: {e}")
