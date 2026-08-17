import os
import asyncio
import time
from .config import validate_config, BACKOFF_BASE
from .logging_config import logger
from .telemetry import telemetry
from .browser_manager import BrowserManager
from .rate_limiter import RateLimiter
from .scan_executor import ScanExecutor
from .result_classifier import ScanStatus
from .deal_validator import DealValidator
from .affiliate_service import AffiliateService, AffiliateServiceException
from .persistence import PersistenceLayer, PersistenceException
from .retry_manager import RetryManager
from .shutdown import GracefulShutdown

class ProductionWorker:
    def __init__(self):
        self.browser_manager = BrowserManager()
        self.rate_limiter = RateLimiter()
        self.shutdown_handler = GracefulShutdown()
        self.is_dry_run = os.getenv('DRY_RUN', '1') == '1'
        
        # Metrics
        self.metrics = {
            "discovered": 0,
            "scheduled": 0,
            "successful": 0,
            "not_found": 0,
            "access_blocked": 0,
            "temporary_failure": 0,
            "parse_failure": 0,
            "rate_limited": 0,
            "retries": 0,
            "total_duration_ms": 0
        }

    async def run(self, job_queue):
        """
        Main worker loop. 
        job_queue: List of URLs or discovery jobs to process.
        """
        logger.info(f"Starting Production Worker (DRY_RUN={self.is_dry_run})")
        telemetry.send_heartbeat("STARTING")
        
        await self.browser_manager.start()
        telemetry.send_heartbeat("RUNNING")
        
        self.metrics["discovered"] = len(job_queue)
        
        tasks = []
        for job in job_queue:
            if self.shutdown_handler.stop_requested:
                break
            
            task = asyncio.create_task(self.process_job(job))
            tasks.append(task)
            self.metrics["scheduled"] += 1
            
        # Wait for all scheduled tasks to complete
        await asyncio.gather(*tasks, return_exceptions=True)
        
        await self.browser_manager.stop()
        telemetry.send_heartbeat("IDLE", metrics=self.metrics)
        self.print_summary()

    async def process_job(self, url: str, attempt=1):
        """Process a single URL through the full pipeline."""
        if self.shutdown_handler.stop_requested:
            return

        start_time = time.time()
        await self.rate_limiter.acquire()
        
        try:
            # 1. Fetch Page
            page = await self.browser_manager.get_page()
            
            # 2. Execute Scan
            scan_result = await ScanExecutor.execute_scan(page, url)
            duration_ms = int((time.time() - start_time) * 1000)
            self.metrics["total_duration_ms"] += duration_ms
            
            # Record Result Metric
            if scan_result.status == ScanStatus.SUCCESS:
                self.metrics["successful"] += 1
            elif scan_result.status == ScanStatus.NOT_FOUND:
                self.metrics["not_found"] += 1
            elif scan_result.status == ScanStatus.ACCESS_BLOCKED:
                self.metrics["access_blocked"] += 1
            elif scan_result.status == ScanStatus.TEMPORARY_FAILURE:
                self.metrics["temporary_failure"] += 1
            elif scan_result.status == ScanStatus.PARSE_FAILURE:
                self.metrics["parse_failure"] += 1
            elif scan_result.status == ScanStatus.RATE_LIMITED:
                self.metrics["rate_limited"] += 1

            logger.info("Scan completed", extra={
                "url": url, 
                "result": scan_result.status.value, 
                "duration_ms": duration_ms,
                "error": scan_result.error
            })

            # Handle Failures & Retries
            if scan_result.status != ScanStatus.SUCCESS:
                if RetryManager.should_retry(attempt, scan_result.status.value):
                    self.metrics["retries"] += 1
                    backoff = (BACKOFF_BASE ** attempt)
                    logger.warning(f"Retrying {url} in {backoff}s (Attempt {attempt})")
                    
                    if scan_result.status == ScanStatus.ACCESS_BLOCKED:
                        # Degradation detected - force context recycle
                        await self.browser_manager.recycle_context_now()
                        telemetry.send_heartbeat("DEGRADED")
                        
                    await asyncio.sleep(backoff)
                    # Release semaphore before recursing so we don't deadlock
                    self.rate_limiter.release()
                    await self.process_job(url, attempt + 1)
                    return
                else:
                    return # Max retries reached or non-retryable failure
                    
            # 3. Deal Validation
            deal_data = scan_result.data
            is_valid = DealValidator.validate(deal_data)
            
            if not is_valid:
                logger.info("Deal failed mathematical validation", extra={"url": url})
                return
                
            # 4. Affiliate Validation
            try:
                # We do NOT fallback to raw canonical url here.
                # If affiliate fails, it raises and skips persistence.
                affiliate_url = AffiliateService.generate_affiliate_link(url)
            except AffiliateServiceException as e:
                # Push to Retry/Hold Queue conceptually
                logger.error(f"Affiliate Quarantine: {e}", extra={"url": url})
                return
                
            # 5. Persistence
            # Generates deterministic observation id for idempotency
            obs_id = RetryManager.generate_observation_id(url, "amazon", deal_data['price'])
            deal_data['observation_id'] = obs_id
            
            PersistenceLayer.save_deal(deal_data, affiliate_url)
            logger.info("Deal persisted successfully", extra={"url": url, "observation_id": obs_id})

        finally:
            # Important: always release concurrency token
            self.rate_limiter.release()

    def print_summary(self):
        avg_scan = 0
        if self.metrics["scheduled"] > 0:
            avg_scan = self.metrics["total_duration_ms"] / self.metrics["scheduled"] / 1000
            
        print("\n" + "="*40)
        print("WORKER DRY-RUN SUMMARY")
        print("="*40)
        print(f"Discovered:        {self.metrics['discovered']}")
        print(f"Scheduled:         {self.metrics['scheduled']}")
        print(f"Successful:        {self.metrics['successful']}")
        print(f"Not Found:         {self.metrics['not_found']}")
        print(f"Access Blocked:    {self.metrics['access_blocked']}")
        print(f"Temporary Failure: {self.metrics['temporary_failure']}")
        print(f"Parse Failure:     {self.metrics['parse_failure']}")
        print(f"Rate Limited:      {self.metrics['rate_limited']}")
        print("-"*40)
        print(f"Average scan:      {avg_scan:.2f}s")
        print(f"Retries:           {self.metrics['retries']}")
        print("="*40 + "\n")

if __name__ == "__main__":
    validate_config()
    worker = ProductionWorker()
    
    logger.info("Worker Service Started. Polling for jobs...")
    
    async def worker_loop():
        while not worker.shutdown_handler.stop_requested:
            try:
                jobs = PersistenceLayer.claim_jobs()
                if not jobs:
                    await asyncio.sleep(10)
                    continue

                for job in jobs:
                    if worker.shutdown_handler.stop_requested:
                        break
                        
                    job_id = job.get('job_id')
                    job_type = job.get('type')
                    payload = job.get('payload', {})
                    
                    logger.info(f"Processing job {job_id} of type {job_type}")
                    
                    # Start Heartbeat Task
                    cancel_requested = False
                    async def heartbeat_loop():
                        nonlocal cancel_requested
                        while not cancel_requested:
                            await asyncio.sleep(15)
                            # True means continue, False means cancel
                            if not PersistenceLayer.heartbeat(job_id):
                                cancel_requested = True
                                logger.info(f"Cancellation requested for job {job_id}")
                                break
                                
                    heartbeat_task = asyncio.create_task(heartbeat_loop())
                    
                    try:
                        if job_type == 'URL_SCAN':
                            url = payload.get('url')
                            if url:
                                await worker.run([url])
                                PersistenceLayer.update_job_status(job_id, 'COMPLETED')
                            else:
                                PersistenceLayer.update_job_status(job_id, 'FAILED')
                                
                        elif job_type in ['AUTO_HUNT', 'CUSTOM_HUNT']:
                            # For now, just mark completed. In future phases, invoke hunter.py logic
                            logger.info(f"Hunt requested: {payload}")
                            PersistenceLayer.update_job_status(job_id, 'COMPLETED')
                            
                        elif job_type == 'CANCELLATION':
                            logger.info("Global cancellation requested by admin.")
                            worker.shutdown_handler.stop_requested = True
                            PersistenceLayer.update_job_status(job_id, 'COMPLETED')
                            
                        elif job_type == 'SYSTEM_COMMAND':
                            # E.g. start scraper (already running if we are here)
                            PersistenceLayer.update_job_status(job_id, 'COMPLETED')
                            
                        else:
                            logger.warning(f"Unknown job type {job_type}")
                            PersistenceLayer.update_job_status(job_id, 'FAILED')
                            
                    except Exception as e:
                        logger.error(f"Error processing job {job_id}: {e}")
                        PersistenceLayer.update_job_status(job_id, 'FAILED')
                    finally:
                        cancel_requested = True # Stop heartbeat
                        await heartbeat_task
                        
            except Exception as e:
                logger.error(f"Error in worker loop: {e}")
                await asyncio.sleep(10)
                
    asyncio.run(worker_loop())
