from worker.new.sdk.discovery.engine.capability_matrix import CapabilityMatrix

def get_capabilities() -> CapabilityMatrix:
    matrix = CapabilityMatrix("Ajio")
    matrix.register_capability('extraction', True)
    matrix.register_capability('discovery', True)
    return matrix
