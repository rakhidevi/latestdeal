import asyncio
import sqlite3
import json
import urllib.request
import urllib.error
from urllib.parse import urljoin, urlparse
from playwright.async_api import async_playwright

BASE_URL = "http://localhost:8000"
DB_PATH = "database/database.sqlite"

class ComplianceAudit:
    def __init__(self):
        self.db = sqlite3.connect(DB_PATH)
        self.db.row_factory = sqlite3.Row
        self.report = {
            "crawl_audit": [],
            "sitemap_audit": [],
            "ad_inventory_audit": [],
            "replicated_content_audit": [],
            "internal_link_audit": [],
            "errors": []
        }

    def fetch_sitemap_urls(self):
        sitemap_url = urljoin(BASE_URL, "/sitemap.xml")
        urls = []
        try:
            req = urllib.request.Request(sitemap_url, headers={'User-Agent': 'LatestDeal-Auditor/1.0'})
            with urllib.request.urlopen(req) as response:
                content = response.read().decode('utf-8')
                # Naive extraction for the audit
                import xml.etree.ElementTree as ET
                root = ET.fromstring(content)
                for child in root:
                    if 'url' in child.tag:
                        for sub in child:
                            if 'loc' in sub.tag:
                                urls.append(sub.text)
        except urllib.error.URLError as e:
            self.report["errors"].append(f"Could not fetch sitemap at {sitemap_url}: {e}")
        except Exception as e:
            self.report["errors"].append(f"Error parsing sitemap: {e}")
        return urls

    async def run_crawls(self):
        print("Starting Phase 9 Compliance Audit...")
        
        # 1. Sitemap Consistency
        sitemap_urls = self.fetch_sitemap_urls()
        self.report["sitemap_audit"].append(f"Found {len(sitemap_urls)} URLs in sitemap.")
        
        # Cross check Deals in DB
        cursor = self.db.cursor()
        
        # Check if slug exists, fallback to id for urls if needed
        cursor.execute("PRAGMA table_info(deals)")
        has_slug = any(row['name'] == 'slug' for row in cursor.fetchall())
        url_col = "slug" if has_slug else "id"

        # Find deals that ARE publishable
        cursor.execute(f"SELECT {url_col} as url_key, id FROM deals WHERE editorial_status = 'PUBLISHED' AND status != 'expired'")
        published_deals = cursor.fetchall()
        
        # Find deals that are NOT publishable
        cursor.execute(f"SELECT {url_col} as url_key, id FROM deals WHERE editorial_status != 'PUBLISHED'")
        unpublished_deals = cursor.fetchall()

        # Check that unpublished deals are NOT in sitemap
        leaked = 0
        for d in unpublished_deals:
            expected_url = f"{BASE_URL}/deals/{d['url_key']}"
            if expected_url in sitemap_urls:
                self.report["sitemap_audit"].append(f"FAIL: Unpublished deal {d['id']} found in sitemap: {expected_url}")
                leaked += 1
                
        if leaked == 0:
            self.report["sitemap_audit"].append("PASS: Zero unpublished deals leaked into sitemap.")

        # 2. Ad Inventory & Replicated Content & Internal Links using Playwright
        async with async_playwright() as p:
            browser = await p.chromium.launch(headless=True)
            context = await browser.new_context()
            page = await context.new_page()
            
            # Check Homepage
            try:
                resp = await page.goto(BASE_URL)
                self.report["crawl_audit"].append(f"Homepage status: {resp.status}")
                
                # Check required footer links (Internal Ecosystem)
                footer_text = await page.inner_text("footer") if await page.query_selector("footer") else ""
                required_pages = ["About", "Editorial Policy", "Affiliate", "Contact"]
                missing = [rp for rp in required_pages if rp.lower() not in footer_text.lower()]
                if missing:
                    self.report["internal_link_audit"].append(f"FAIL: Missing required pages in footer: {missing}")
                else:
                    self.report["internal_link_audit"].append("PASS: All required ecosystem pages found in footer.")
            except Exception as e:
                 self.report["errors"].append(f"Homepage crawl failed: {e}")

            # Check a published deal (Ad Inventory & Content)
            if published_deals:
                test_deal = published_deals[0]
                url = f"{BASE_URL}/deals/{test_deal['url_key']}"
                try:
                    resp = await page.goto(url)
                    content = await page.content()
                    
                    # Check AdSense eligibility
                    has_adsense = "adsbygoogle" in content or "x-ad-banner" in content
                    if has_adsense:
                        self.report["ad_inventory_audit"].append(f"PASS: Ads rendered on eligible published deal {url}")
                    else:
                        self.report["ad_inventory_audit"].append(f"NOTE: No ads found on {url} (expected if not integrated yet)")
                        
                    # Check Replicated Content (Naive check)
                    prose_text = await page.inner_text("body")
                    if len(prose_text) < 500:
                        self.report["replicated_content_audit"].append(f"FAIL: Page {url} has very thin content ({len(prose_text)} chars).")
                    else:
                        self.report["replicated_content_audit"].append(f"PASS: Page {url} contains substantial text ({len(prose_text)} chars).")
                        
                except Exception as e:
                    self.report["errors"].append(f"Failed crawling deal {url}: {e}")
            else:
                self.report["ad_inventory_audit"].append("SKIP: No PUBLISHED deals found in database to audit.")

            # Check an unpublished deal (Should be 404 or NOINDEX)
            if unpublished_deals:
                test_raw = unpublished_deals[0]
                url = f"{BASE_URL}/deals/{test_raw['url_key']}"
                try:
                    resp = await page.goto(url)
                    if resp.status == 404:
                        self.report["crawl_audit"].append(f"PASS: Raw deal correctly returned 404: {url}")
                    else:
                        content = await page.content()
                        if "noindex" in content.lower():
                            self.report["crawl_audit"].append(f"PASS: Raw deal has NOINDEX tag: {url}")
                        else:
                            self.report["crawl_audit"].append(f"FAIL: Raw deal is 200 OK and missing NOINDEX: {url}")
                except Exception as e:
                    self.report["errors"].append(f"Failed crawling raw deal {url}: {e}")

            await browser.close()
            
        self.generate_markdown_report()

    def generate_markdown_report(self):
        report_md = "# Phase 9 Audit Report\n\n"
        
        for section, lines in self.report.items():
            report_md += f"## {section.replace('_', ' ').title()}\n"
            if not lines:
                report_md += "- No findings.\n"
            for line in lines:
                report_md += f"- {line}\n"
            report_md += "\n"
            
        with open("phase9_audit_report.md", "w") as f:
            f.write(report_md)
        print("Audit complete! Report saved to phase9_audit_report.md")

if __name__ == "__main__":
    auditor = ComplianceAudit()
    asyncio.run(auditor.run_crawls())
