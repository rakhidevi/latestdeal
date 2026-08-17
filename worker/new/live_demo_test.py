import sys, os, json
project_root = r"K:\WhatsAppUtility\LatestDeal"
sys.path.insert(0, project_root)

_CATEGORIES_FILE = os.path.join(project_root, "worker", "new", "knowledge", "amazon", "compiled", "categories_v3.json")
with open(_CATEGORIES_FILE, "r", encoding="utf-8") as f:
    data = json.load(f)

cat_count = len(data["categories"])
brand_targets = sum(len(c.get("brands", [])) for c in data["categories"])
print(f"Categories: {cat_count}")
print(f"Brand x Category scan targets: {brand_targets}")
print(f"Total scan targets: {cat_count + brand_targets}")
print()
for cat in data["categories"]:
    print(f"  [{cat['id']}] {cat['name']} — {len(cat.get('brands', []))} brands — node:{cat['amazon_node']}")
