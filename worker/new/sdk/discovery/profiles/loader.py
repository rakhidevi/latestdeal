import yaml
from typing import List, Dict, Any
from pathlib import Path
from worker.new.sdk.foundation.dto.models import DiscoveryProfileDTO

class ProfileLoader:
    """Loads Discovery Profiles from YAML configuration files."""
    
    @classmethod
    def load_from_file(cls, filepath: str) -> DiscoveryProfileDTO:
        with open(filepath, 'r') as f:
            data = yaml.safe_load(f)
            
        # Ensure schema versioning is respected
        if data.get('schema') != 1:
            raise ValueError(f"Unsupported schema version: {data.get('schema')} in {filepath}")
            
        return DiscoveryProfileDTO(**data)

    @classmethod
    def load_all_from_directory(cls, directory: str) -> List[DiscoveryProfileDTO]:
        profiles = []
        path = Path(directory)
        if not path.exists():
            return profiles
            
        for file in path.glob("*.yaml"):
            try:
                profiles.append(cls.load_from_file(str(file)))
            except Exception as e:
                # In real scenario, log this via telemetry
                print(f"Error loading profile {file}: {e}")
                
        return profiles
