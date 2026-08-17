import os

files = [
    "AssetManager.php",
    "AutomationEngine.php",
    "CampaignBuilder.php",
    "ThemeManager.php"
]

base_dir = r"k:\WhatsAppUtility\LatestDeal\backend\app\Livewire\Admin\Marketing"

for filename in files:
    filepath = os.path.join(base_dir, filename)
    if not os.path.exists(filepath):
        continue
        
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
        
    if "Livewire\\Attributes\\Middleware" not in content:
        # Insert the use statement
        content = content.replace("use Livewire\\Component;", "use Livewire\\Component;\nuse Livewire\\Attributes\\Middleware;")
        # Insert the attribute above class declaration
        content = content.replace("class " + filename.replace('.php', ''), "#[Middleware('studio.admin')]\nclass " + filename.replace('.php', ''))
        
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)

print("Middleware attributes applied to Marketing components.")
