import subprocess
import json
import sys
import re

profiles = [
    # (brand, category, min_discount, query)
    ("LG", "", 0, "LG"),
    ("Samsung", "", 0, "Samsung"),
    ("LG", "Appliances", 0, "LG Appliances"),
    ("Samsung", "TV", 0, "Samsung TV"),
]

results = []

print("PHASE 18 VALIDATION")
print("------------------------------------")

total_discovered = 0
total_valid = 0
total_invalid = 0
total_duplicates = 0
total_extraction_failures = 0
profiles_tested = set()

for i, profile in enumerate(profiles):
    brand, category, discount, query = profile
    name = f"{brand or ''} {category or ''} >= {discount}%".strip()
    
    # Run the pipeline script
    cmd = ["python", "run_discovery_pipeline.py", f"--brand={brand}", f"--category={category}", f"--discount={discount}", f"--query={query}"]
    
    print(f"\n{name}")
    try:
        output = subprocess.check_output(cmd, stderr=subprocess.STDOUT, text=True)
        
        # Parse the summary output
        discovered = int(re.search(r'Discovered:\s+(\d+)', output).group(1))
        valid = int(re.search(r'Valid:\s+(\d+)', output).group(1))
        invalid = int(re.search(r'Profile validation failures:\s+(\d+)', output).group(1))
        duplicates = int(re.search(r'Duplicates:\s+(\d+)', output).group(1))
        extraction_failures = int(re.search(r'Extraction failures:\s+(\d+)', output).group(1))
        
        print(f"  Discovered:            {discovered}")
        print(f"  Extraction failures:   {extraction_failures}")
        print(f"  Duplicates:            {duplicates}")
        print(f"  Profile validation:    {invalid}")
        print(f"  Valid:                 {valid}")
        print(f"  --------------------------")
        print(f"  Total:                 {extraction_failures + duplicates + invalid + valid}")
        
    except subprocess.CalledProcessError as e:
        print(f"  FAILED! Output:\n{e.output}")
        sys.exit(1)
    except Exception as e:
        print(f"  FAILED! {e}")
        sys.exit(1)

print("\n────────────────────────────────────")
print(f"Profiles tested:       {len(profiles_tested)}")
print(f"Total discovered:      {total_discovered}")
print(f"Total valid:           {total_valid}")
print(f"Total invalid:         {total_invalid}")
print(f"Duplicate ASINs:       {total_duplicates}")
print(f"Extraction failures:   {total_extraction_failures}")
print("────────────────────────────────────")
