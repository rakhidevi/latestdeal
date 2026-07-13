import requests
import subprocess

def get_deals():
    cmd = ['docker', 'exec', 'latestdeal_mysql', 'mysql', '-uadmin', '-pdev_password', 'latestdeal', '-e', 'SELECT id, url, short_url FROM deals;', '--batch', '--silent']
    res = subprocess.run(cmd, capture_output=True, text=True)
    deals = []
    for line in res.stdout.strip().split('\n'):
        if line:
            parts = line.split('\t')
            if len(parts) >= 3:
                deals.append({'id': parts[0], 'url': parts[1], 'short_url': parts[2]})
    return deals

deals = get_deals()
to_delete = []

print(f'Checking {len(deals)} deals...')
for deal in deals:
    urls_to_check = [deal['url'], deal['short_url']]
    found = False
    for u in urls_to_check:
        if not u or u.lower() == 'null': continue
        try:
            r = requests.get(u, allow_redirects=True, timeout=10)
            if 'indiafreestuff' in r.url:
                found = True
                break
        except Exception as e:
            pass
    if found:
        print(f"Deal {deal['id']} redirects to indiafreestuff")
        to_delete.append(deal['id'])

if to_delete:
    print('Deleting IDs:', to_delete)
    ids_str = ','.join(to_delete)
    del_cmd = ['docker', 'exec', 'latestdeal_mysql', 'mysql', '-uadmin', '-pdev_password', 'latestdeal', '-e', f'DELETE FROM deals WHERE id IN ({ids_str});']
    subprocess.run(del_cmd)
    print('Deleted successfully.')
else:
    print('No deals found redirecting to indiafreestuff.')
