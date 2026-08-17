import unittest
from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker
from worker.new.sdk.discovery.knowledge.database.models import Base, Brand, Category, SearchTargetRecord

class TestDatabaseModels(unittest.TestCase):
    def setUp(self):
        self.engine = create_engine("sqlite:///:memory:")
        Base.metadata.create_all(self.engine)
        self.SessionLocal = sessionmaker(bind=self.engine)

    def test_create_brand(self):
        db = self.SessionLocal()
        brand = Brand(name="Samsung", tier="premium", aliases=["samsung electronics"])
        db.add(brand)
        db.commit()
        
        saved_brand = db.query(Brand).filter(Brand.name == "Samsung").first()
        self.assertIsNotNone(saved_brand)
        self.assertEqual(saved_brand.tier, "premium")
        self.assertIn("samsung electronics", saved_brand.aliases)
        db.close()

    def test_search_target_record(self):
        db = self.SessionLocal()
        record = SearchTargetRecord(
            id="test-id",
            trace_id="trc-test",
            provider="amazon",
            url="http://test.com",
            parameters={"brand": "Samsung"}
        )
        db.add(record)
        db.commit()
        
        saved_record = db.query(SearchTargetRecord).first()
        self.assertEqual(saved_record.trace_id, "trc-test")
        self.assertEqual(saved_record.parameters["brand"], "Samsung")
        db.close()

if __name__ == '__main__':
    unittest.main()
