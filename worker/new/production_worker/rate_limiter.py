import asyncio
from .config import MAX_CONCURRENT_SCANS

class RateLimiter:
    def __init__(self):
        self.semaphore = asyncio.Semaphore(MAX_CONCURRENT_SCANS)
        
    async def acquire(self):
        await self.semaphore.acquire()
        
    def release(self):
        self.semaphore.release()
