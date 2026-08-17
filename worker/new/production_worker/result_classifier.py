from enum import Enum

class ScanStatus(Enum):
    SUCCESS = "SUCCESS"
    NOT_FOUND = "NOT_FOUND"
    TEMPORARY_FAILURE = "TEMPORARY_FAILURE"
    RATE_LIMITED = "RATE_LIMITED"
    ACCESS_BLOCKED = "ACCESS_BLOCKED"
    PARSE_FAILURE = "PARSE_FAILURE"

class ScanResult:
    def __init__(self, status: ScanStatus, data=None, error=None):
        self.status = status
        self.data = data
        self.error = error
