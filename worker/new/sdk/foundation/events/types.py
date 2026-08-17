from enum import Enum

class DiscoveryEvent(str, Enum):
    # Search Target Lifecycle
    SEARCH_TARGET_GENERATED = "SearchTargetGenerated"
    SEARCH_TARGET_QUEUED = "SearchTargetQueued"
    
    # Extraction Lifecycle
    EXTRACTION_STARTED = "ExtractionStarted"
    PRODUCT_EXTRACTED = "ProductExtracted"
    
    # Validation Lifecycle
    VALIDATION_STARTED = "ValidationStarted"
    OPPORTUNITY_DETECTED = "OpportunityDetected"
    DEAL_VALIDATED = "DealValidated"
    DEAL_REJECTED = "DealRejected"
    
    # Publishing Lifecycle
    PUBLISH_REQUESTED = "PublishRequested"
    DEAL_PUBLISHED = "DealPublished"
    DEAL_UPDATED = "DealUpdated"
    DEAL_EXPIRED = "DealExpired"
