from typing import Dict, Any, List, Optional
from datetime import datetime, timezone
from worker.new.sdk.foundation.identity.generator import generate_uuid

class PolicyVersion:
    def __init__(self, version: str, rules: Dict[str, Any], created_by: str):
        self.id = generate_uuid()
        self.version = version
        self.rules = rules
        self.created_by = created_by
        self.created_at = datetime.now(timezone.utc)
        self.status = "DRAFT" # DRAFT, ACTIVE, DEPRECATED, ARCHIVED

class DecisionGovernanceManager:
    """
    Decision Governance Module (PRR Requirement).
    Manages operational governance of intelligence tuning without touching code.
    Provides strict versioning and approval workflows for Policies and Strategies.
    """
    
    def __init__(self):
        self._policies: Dict[str, PolicyVersion] = {}
        self._active_policy_version: Optional[str] = None
        
    def create_policy_version(self, version: str, rules: Dict[str, Any], user: str) -> PolicyVersion:
        """Creates a new policy version in DRAFT state."""
        if version in self._policies:
            raise ValueError(f"Policy version {version} already exists.")
            
        policy = PolicyVersion(version, rules, user)
        self._policies[version] = policy
        return policy
        
    def approve_policy(self, version: str, approver: str) -> None:
        """Approves and activates a policy version. Deprecates the old one."""
        if version not in self._policies:
            raise ValueError("Policy version not found.")
            
        policy = self._policies[version]
        if policy.status != "DRAFT":
            raise ValueError(f"Cannot approve policy in {policy.status} state.")
            
        if self._active_policy_version:
            old_policy = self._policies[self._active_policy_version]
            old_policy.status = "DEPRECATED"
            
        policy.status = "ACTIVE"
        self._active_policy_version = version
        
    def get_active_policy(self) -> Optional[PolicyVersion]:
        """Returns the currently active policy."""
        if not self._active_policy_version:
            return None
        return self._policies[self._active_policy_version]
        
    def rollback_policy(self, target_version: str, reason: str) -> None:
        """Rolls back to a previous version."""
        if target_version not in self._policies:
            raise ValueError("Target policy version not found.")
            
        target = self._policies[target_version]
        if target.status not in ["DEPRECATED", "ACTIVE"]:
            raise ValueError("Can only rollback to previously active policies.")
            
        if self._active_policy_version:
            current = self._policies[self._active_policy_version]
            current.status = "ARCHIVED"
            
        target.status = "ACTIVE"
        self._active_policy_version = target_version
