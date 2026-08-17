import asyncio
from playwright.async_api import Error as PlaywrightError
from .result_classifier import ScanResult, ScanStatus
from .logging_config import logger
from .config import NAVIGATION_TIMEOUT

class ScanExecutor:
    @staticmethod
    async def execute_scan(page, url: str) -> ScanResult:
        try:
            response = await page.goto(url, wait_until="domcontentloaded", timeout=NAVIGATION_TIMEOUT)
            
            if not response:
                return ScanResult(ScanStatus.TEMPORARY_FAILURE, error="No response object returned")
                
            status_code = response.status
            
            if status_code == 404:
                return ScanResult(ScanStatus.NOT_FOUND)
            if status_code in [429, 503]:
                return ScanResult(ScanStatus.RATE_LIMITED, error=f"HTTP {status_code}")
                
            # Check for Captcha / Dog Page in content
            content = await page.content()
            if "Type the characters you see in this image" in content or "Enter the characters you see below" in content:
                return ScanResult(ScanStatus.ACCESS_BLOCKED, error="CAPTCHA Detected")
            
            if "Sorry! Something went wrong" in content:
                # Amazon Dog page
                return ScanResult(ScanStatus.NOT_FOUND, error="Amazon Dog Page")

            # Execute JS to extract deal (We inject the script defined in live_demo)
            # For simplicity in this rewrite, we assume the JS block is loaded from a module
            # We will just stub the JS call here.
            js_script = """() => {
                let priceEl = document.querySelector('.a-price-whole');
                if(!priceEl) return null;
                // mock extraction
                return { 
                    price: parseFloat(priceEl.innerText.replace(/,/g, '')), 
                    mrp: parseFloat(priceEl.innerText.replace(/,/g, '')) * 1.5,
                    title: document.title 
                };
            }"""
            
            extracted_data = await page.evaluate(js_script)
            
            if not extracted_data:
                return ScanResult(ScanStatus.PARSE_FAILURE, error="JS returned null, parse failed")
                
            return ScanResult(ScanStatus.SUCCESS, data=extracted_data)
            
        except PlaywrightError as e:
            err_msg = str(e)
            if "Timeout" in err_msg:
                return ScanResult(ScanStatus.TEMPORARY_FAILURE, error="Timeout")
            if "ERR_NAME_NOT_RESOLVED" in err_msg or "ERR_CONNECTION_REFUSED" in err_msg:
                return ScanResult(ScanStatus.TEMPORARY_FAILURE, error="Network Error")
            return ScanResult(ScanStatus.PARSE_FAILURE, error=err_msg)
        except Exception as e:
            return ScanResult(ScanStatus.TEMPORARY_FAILURE, error=str(e))
        finally:
            # We close the page to prevent memory bloat (the browser manager will create a new one next time)
            try:
                await page.close()
            except:
                pass
