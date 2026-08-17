import os
from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker, DeclarativeBase

# Use SQLite by default for development, but schema is designed for PostgreSQL
DB_URL = os.getenv("DATABASE_URL", "sqlite:///k:/WhatsAppUtility/LatestDeal/deals.db")

engine = create_engine(DB_URL, echo=False)
SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)

class Base(DeclarativeBase):
    pass

def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()
