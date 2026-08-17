# ADR 0001: Platform Foundation & Golden Rule

## Status
Accepted

## Context
We are migrating the legacy LatestDeal scraper to a Universal Commerce Discovery Platform. The risk of disrupting the current production data pipeline is high if we replace the scraping logic immediately. We need a way to introduce advanced Discovery intelligence while isolating the legacy extraction system.

## Decision
We will adopt the **Strangler Fig Pattern**. 
The Universal Commerce Discovery Platform SHALL NEVER modify, replace, or directly depend on the existing production scraper. 

The architecture strictly decouples:
1. Discovery (Produces SearchTargets)
2. Extraction (Produces Products)
3. Validation (Produces Opportunities)
4. Publishing (Produces Deal Events)

Communication between these modules occurs exclusively through **DTOs** and an **Event Bus**.

### Phase 0: Platform Foundation
Before any business logic is written, we must build a Foundation layer (`contracts`, `dto`, `events`, `telemetry`, `identity`). This layer contains no provider logic.

### Compatibility Layer
All new Discovery targets will flow through a `Compatibility Layer` that maps the new `SearchTargetDTO` to the legacy pipeline. 

### Shadow Mode
The new system will operate in "Shadow Mode" first—generating targets and producing telemetry without actually injecting them into the legacy queue. Only after stability is proven will it go live.

## Consequences
- **Positive:** Zero risk to current production revenue or operations. We can test discovery logic safely.
- **Positive:** New modules are highly decoupled and testable.
- **Negative:** Requires mapping DTOs back and forth to legacy formats for the duration of the migration.
