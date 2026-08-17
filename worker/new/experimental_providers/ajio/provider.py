from worker.new.sdk.foundation.provider import BaseProvider
from .manifest import get_manifest
from .capabilities import get_capabilities
from .compatibility_layer import AjioCompatibilityLayer

class AjioProvider(BaseProvider):
    def __init__(self):
        super().__init__(get_manifest())
        self.capabilities = get_capabilities()
        self.compatibility = AjioCompatibilityLayer()
    
    def discover(self):
        raise NotImplementedError()
        
    def extract(self):
        raise NotImplementedError()
