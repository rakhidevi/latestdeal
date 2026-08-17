# LatestDeal Workspace Rules

These are strict rules that MUST be followed for all future development in this workspace. Do not deviate from these rules under any circumstances.

---

## 1. Browser Automation (Playwright) Strict Requirements
- **Real Windows Chrome**: NEVER use the default Playwright Chromium binary. You MUST explicitly use the user's real local Windows Chrome installation: `executable_path=r"C:\Program Files\Google\Chrome\Application\chrome.exe"`.
- **Bot Stealth**: ALWAYS pass `args=["--disable-blink-features=AutomationControlled"]` to prevent detection.
- **Hide Automation Warnings**: ALWAYS pass `ignore_default_args=["--enable-automation", "--no-sandbox"]` when launching persistent contexts to prevent the "unsupported command-line flag" or "automated test software" banners from showing up on the user's screen.
- **Visible Mode**: `headless=False` MUST be used so the user can see the browser and solve CAPTCHAs or login if necessary (unless explicitly instructed otherwise).

---

## 2. Playwright Tab Management (Memory Leaks)
- **Do NOT blindly use `new_page()`**: When using a persistent context (`launch_persistent_context`), Playwright automatically creates a default `about:blank` tab. If you call `new_page()` for every scraping task, Chrome will accumulate thousands of blank tabs because it remembers session state.
- **Rule**: ALWAYS re-use the first existing tab instead of creating a new one:
  ```python
  page = context.pages[0] if context.pages else context.new_page()
  ```
- Never leave unused tabs open.
- Always close temporary pages after use.

---

## 3. Architecture & APIs
- **No External APIs for Data**: Avoid using 3rd party APIs (like the Impact Radius API) to fetch product/course data if it can be directly scraped via Playwright. Playwright is the preferred source of truth to avoid API rate limits, complex authentication, and dependency on external API uptimes.
- **Local AI over Cloud API (Unless Hosted)**: The system currently uses a local `Ollama` instance at `http://localhost:11434`. Do not change this to OpenAI/Cloud APIs unless explicitly migrating the system to a cloud VPS. Never replace local AI with cloud AI unless explicitly instructed.
- Prefer local infrastructure over cloud dependencies whenever practical.

---

## 4. One-Click Worker Ecosystem
- The entire Python scraping infrastructure (Daemon, Dashboard, Telegram Listener, Amazon Hunter) MUST be launchable via the single `START_WORKER.bat` script.
- Do not introduce new background scripts without adding them to `START_WORKER.bat`. The user expects a single double-click to run everything.
- The complete scraping ecosystem must remain one-click executable.

---

## 5. Strict 100% Verification Rule
- Whenever you complete a task based on a requirement document or implementation plan, you MUST perform a line-by-line reverification of every single requirement, UI element, and technical specification mentioned in the document.
- If ANY part is not 100% implemented, you MUST repeat the implementation and verification process until zero percent is missing. You must not claim a task is complete until this exhaustive check confirms perfection.
- Never state that a feature is complete unless all implemented requirements have been verified against the code and any available tests or runtime evidence.
- If verification cannot be completed because required code, environments, or systems are unavailable, explicitly state what could not be verified instead of claiming completion.

---

## 6. Price Extraction
- **MRP** - Always displays like M.R.P.: ₹39,990.
- Do not extract unit prices like (₹17,99,000 /100 g) as MRP.
- Always extract the official **M.R.P.** value.
- Never confuse current selling price with original MRP.
- Price extraction must support future retailer expansion.

---

## 7. 3-Tier Git & Deployment Pipeline (Dev ➔ Staging ➔ Production)
- **Step 1 — Local Development (`dev` branch)**: ALL new features, code modifications, and bug fixes MUST be committed and pushed to the `dev` branch (`origin/dev`).
- **Step 2 — Staging Verification (`staging` branch)**: Once feature work on `dev` is complete, merge `dev` into `staging` and push to `origin/staging` to trigger the automated Staging deployment workflow ([.github/workflows/deploy-staging.yml](file:///k:/WhatsAppUtility/LatestDeal/.github/workflows/deploy-staging.yml)). Perform full 100% line-by-line reverification on `staging` environment first.
- **Step 3 — Production Release (`main` branch)**: NEVER push directly to `main` or `origin/main` from local development. Only merge `staging` into `main` and push to `origin/main` when the user explicitly commands "push to production" or "deploy to production".

---

## 8. Implementation Sign-off
- **Acknowledge Rules**: After completing any implementation or task, you MUST explicitly mention in your final response to the user that you have read and followed all the instructions and rules in the `AGENTS.md` file.
- Summarize completed work.
- Summarize verification performed.
- Clearly list any known limitations or items not verified.
- Do not claim to have read AGENTS.md unless it was actually available.

---

## 9. Layered Architecture
- Business logic MUST NEVER exist inside Controllers, Livewire Components, Blade Templates, or Console Commands.
- Business logic belongs inside Services, State Machines, Strategies, Providers, Policies, or Domain classes.
- UI layers are responsible only for presentation and orchestration.

---

## 10. DTO First
- Services MUST return DTOs instead of associative arrays.
- Avoid passing raw arrays between layers.
- Every major module should expose strongly typed DTOs.

---

## 11. Provider Pattern
- Any feature with multiple possible implementations MUST use interfaces.
- Examples include:
  - Mail Providers
  - Storage Providers
  - Queue Providers
  - AI Providers
  - Browser Providers
  - Notification Providers

---

## 12. Single Source of Truth
- Every business rule must exist in exactly one location.
- Never duplicate calculations across Dashboard, Analytics, Campaigns, or Reports.
- Shared logic must always be extracted into reusable services.

---

## 13. Zero Duplicate Business Logic
- If identical logic exists in more than one place, it MUST be extracted into a shared service.
- Avoid duplicated validation, rendering, parsing, calculations, and transformations.

---

## 14. State Machine Rule
- Any object with more than four states MUST use a State Machine.
- Never scatter status checks throughout the project.
- Campaigns, Subscribers, Workers, Providers, and Automations should all use state machines.

---

## 15. Rendering Pipeline
- All email rendering MUST pass through a single rendering pipeline.

```
Template
    ↓
Theme
    ↓
Variables
    ↓
Renderer
    ↓
HTML
```

- Never generate email HTML in multiple locations.

---

## 16. Feature Flags
- Enterprise features must be controlled using feature flags.
- Examples include:
  - AI
  - Automation
  - WhatsApp
  - SMS
  - Analytics
  - Preview Center

---

## 17. Event-Driven Architecture
- Important business actions MUST dispatch domain events.
- Avoid tight coupling between modules.
- Prefer listeners over direct cross-module dependencies.

---

## 18. Queue First
- Operations expected to take longer than two seconds MUST execute as queued jobs.
- Long-running operations should never block the UI.

---

## 19. Idempotent Jobs
- Every queued job MUST be safe to retry.
- Duplicate execution must never produce duplicate data or duplicate emails.

---

## 20. Audit Everything
- Every administrative action must be auditable.
- Record:
  - User
  - Timestamp
  - Entity
  - Action
  - Previous Values
  - New Values

---

## 21. Soft Deletes
- Business data should use soft deletes whenever practical.
- Never permanently delete Campaigns, Templates, Subscribers, Segments, or Assets without explicit approval.

---

## 22. Version Everything
- Maintain version history for:
  - Templates
  - Themes
  - Campaigns
  - Settings
  - AI Prompts
  - Configuration

---

## 23. Configuration Driven Development
- Never hardcode:
  - Batch Sizes
  - Limits
  - Retry Counts
  - Timeouts
  - Provider Priorities
- All configurable values belong in configuration files or the database.

---

## 24. Enum First
- Status values MUST use Enums instead of raw strings.
- Avoid magic string comparisons throughout the application.

---

## 25. Component First UI
- Never duplicate Blade or Livewire UI.
- Build reusable components for:
  - Status Badges
  - Cards
  - Tables
  - Filters
  - Buttons
  - Dialogs
  - Progress Bars
  - Empty States

---

## 26. Responsive Design
- Every page MUST work on Desktop, Tablet, and Mobile.
- Avoid desktop-only interfaces.

---

## 27. Accessibility
- All UI must support:
  - Keyboard navigation
  - Proper focus states
  - ARIA labels
  - Screen readers
  - Accessible color contrast

---

## 28. Empty State Experience
- Never display an empty page.
- Every empty state should provide:
  - Explanation
  - Primary Action
  - Secondary Action
  - Helpful Guidance

---

## 29. Error Handling
- Never expose raw exceptions to users.
- Every error should explain:
  - What happened
  - Why it happened (if known)
  - What the user can do next

---

## 30. Search Everywhere
- Every enterprise list should support:
  - Search
  - Sorting
  - Filtering
  - Pagination
- Design for future support of saved filters.

---

## 31. Omnichannel Architecture
- Never design Email-specific architecture.
- Campaigns should support future delivery channels including:
  - Email
  - SMS
  - Push Notifications
  - WhatsApp
  - Telegram
  - In-App Notifications

---

## 32. AI Safety
- AI may Suggest, Generate, Translate, or Summarize.
- AI must NEVER automatically modify production data without explicit approval.

---

## 33. Testing Standards
- Every feature should include:
  - Unit Tests
  - Feature Tests
  - Livewire Tests
  - End-to-End Tests where appropriate
- Critical business workflows require comprehensive automated test coverage.

---

## 34. Security First
- Every feature must include:
  - Authorization
  - Input Validation
  - CSRF Protection
  - XSS Protection
  - File Validation
  - Rate Limiting where appropriate
- Secrets belong only in environment variables.

---

## 35. Observability
- Critical operations should produce structured logs and metrics.
- Include correlation identifiers where possible.
- Queue workers, scrapers, AI tasks, imports, exports, and campaign sending should all be observable.

---

## 36. Backward Compatibility
- Database changes should be additive whenever practical.
- Avoid breaking changes without migration strategies.
- Deprecate before removing.

---

## 37. Documentation
- Every major architectural change requires an ADR (Architecture Decision Record).
- Complex workflows should include diagrams or sequence documentation.
- Public services should include proper PHPDoc documentation.

---

## 38. Performance First
- Avoid N+1 queries.
- Use eager loading where appropriate.
- Cache expensive computations.
- Optimize database indexes.
- Monitor query performance.
- Optimize assets before delivery.

---

## 39. Database Standards
- Every table must include proper indexes.
- Foreign keys should be enforced where appropriate.
- Migrations must be reversible.
- Seeders should be idempotent.
- Avoid raw SQL unless absolutely necessary.

---

## 40. Code Quality Standards
- Follow PSR standards.
- Keep methods focused on a single responsibility.
- Prefer composition over inheritance.
- Avoid god classes.
- Keep files organized by domain rather than by technical type whenever practical.

---

## 41. Enterprise UX Standards
- Design workflows instead of CRUD pages.
- Reduce unnecessary clicks.
- Maintain consistent spacing, typography, and status indicators.
- Build operational dashboards that prioritize actionable information.

---

## 42. Future-Proof Design
- Every new module should be designed with future extensibility in mind.
- Prefer interfaces, configuration, and modular architecture over hardcoded implementations.
- Build features that can evolve without major refactoring.

---

## 43. Architecture Before Shortcuts
- Never sacrifice architecture for short-term convenience.
- Temporary solutions must be clearly marked with TODOs and documented.
- Long-term maintainability always takes priority over quick fixes.

---

## 44. Shopping Intelligence Platform (AdSense Remediation)
- **Core Objective:** LatestDeal is a Shopping Intelligence Platform, not just an affiliate redirect site. We focus on transparency, trust, and deep price analytics to provide value and secure AdSense approval.
- **No Hallucinations:** When generating AI content (Pros, Cons, Verdicts), never hallucinate product specifications, original prices, or discounts.
- **Value-Added Features:** Deal pages MUST include Commerce Intelligence metrics: Final Verdict, Alternatives, Price Volatility, and algorithmic Buy/Wait Indicators.
- **SEO & Architecture:** Implement SEO Search Hubs for Brands and Categories with unique editorial introductions and FAQs. Always include full Schema.org markup (Organization, WebSite, BreadcrumbList, Product, Offer, Review, FAQPage).
- **Trust Signals:** Ensure Trust Pages (About, Contact, Privacy, Terms, Editorial Policy, Cookie Policy, Corrections Policy) and Affiliate Disclosures are strictly enforced and accessible from the global footer and deal pages.
- **Content Ratio:** Maintain at least a ~40% editorial content ratio by building Evergreen Buying Guides, Educational Blog Posts, and Seasonal Events coverage to balance raw affiliate links.

---

## 45. Monetization & AdSense Compliance (Strict Enforcement)
- **Thin Content & Webmaster Spam Policies:** The platform MUST provide unique, relevant value on every page. Never build features that scrape or aggregate raw affiliate links without augmenting them with proprietary data (e.g., AI Analysis, Price Volatility, Alternative Products). Do not use cloaking, hidden text (`display: none` for keyword stuffing), or doorway pages.
- **Google Publisher Restrictions:** Do NOT aggregate, scrape, or promote restricted content. This includes: Adult/Sexual content, shocking content, explosives, firearms, tobacco, recreational drugs, alcohol sales, gambling, or unapproved prescription drugs. The system strictly aggregates consumer electronics, software, home appliances, and lifestyle goods.
- **AdSense Program Policies:** Ensure clear, non-deceptive site navigation. Do not implement deceptive pop-ups, pop-unders, or mechanisms that encourage invalid clicks. Traffic must remain organic and verified; never use bot traffic generators.

---

## 46. Universal Discovery Framework Freeze Policy
- **Strict Freeze:** No new scheduler abstractions, registries, lifecycle models, or orchestration layers may be introduced to the Universal Discovery Framework unless a new provider or strategy exposes a real limitation that cannot be addressed by the existing framework.
- **Focus:** Future development must focus on implementing high-value concrete discovery strategies (Lightning Deals, Coupons, Warehouse, etc.) and expanding provider coverage, not building new framework infrastructure.
