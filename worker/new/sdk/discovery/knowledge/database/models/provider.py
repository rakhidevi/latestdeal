from sqlalchemy import Column, String, Float, Boolean, JSON, DateTime
from sqlalchemy.sql import func
from worker.new.sdk.discovery.knowledge.database.db import Base
from worker.new.sdk.foundation.identity.generator import generate_uuid

class SellerProfile(Base):
    __tablename__ = "seller_profiles"

    id = Column(String, primary_key=True, default=generate_uuid)
    provider = Column(String, index=True, nullable=False) # e.g. amazon
    seller_id = Column(String, index=True, nullable=False) # Provider's internal ID
    name = Column(String, index=True)
    rating = Column(Float, default=0.0)
    is_trusted = Column(Boolean, default=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(DateTime(timezone=True), onupdate=func.now())

class ProviderNode(Base):
    __tablename__ = "provider_nodes"

    id = Column(String, primary_key=True, default=generate_uuid)
    provider = Column(String, index=True, nullable=False)
    node_id = Column(String, index=True, nullable=False) # e.g. Amazon category node ID
    name = Column(String)
    path = Column(String) # Hierarchy path
    mapped_department_id = Column(String, index=True, nullable=True) # Maps to our internal department
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(DateTime(timezone=True), onupdate=func.now())

class ProviderManifestRecord(Base):
    __tablename__ = "provider_manifests"

    provider = Column(String, primary_key=True)
    version = Column(String, nullable=False)
    status = Column(String, default="active")
    supports_discount = Column(Boolean, default=False)
    supports_brand = Column(Boolean, default=False)
    supports_coupon = Column(Boolean, default=False)
    supports_prime = Column(Boolean, default=False)
    supports_seller = Column(Boolean, default=False)
    supports_node = Column(Boolean, default=False)
    supports_price_band = Column(Boolean, default=False)
    supports_rating = Column(Boolean, default=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(DateTime(timezone=True), onupdate=func.now())
