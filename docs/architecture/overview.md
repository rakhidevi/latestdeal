# Architectural Overview

## Platform Vision
LatestDeal is India's leading AI-powered affiliate deals platform. To support horizontal scaling across dozens of store providers (Amazon, Flipkart, Myntra, etc.) and millions of deals, the architecture relies on strict modularity, asynchronous event-driven flows, and comprehensive observability.

## Core Architectural Principles
1. **Extensibility over Simplicity:** The platform is designed so that adding new stores, AI capabilities, or publishing channels never requires touching the core ingestion logic.
2. **Event-Driven Decoupling:** Ingestion, normalization, validation, scoring, and publishing are entirely decoupled using an Event Bus. No module waits for another to finish its work.
3. **Strangler Fig Migration:** Legacy systems are phased out incrementally by running the new architecture alongside the old until verified.
4. **Strict Interfaces & DTOs:** All data flowing between modules is passed as immutable Data Transfer Objects (DTOs). Providers are forced to adhere to Abstract Base Classes (ABCs).
5. **Observability First:** The platform relies on deep monitoring, `correlation_id` tracking, and provider versioning to quickly identify failures at scale.

## High-Level Architecture
- **Worker Node (Python):** Handles all heavy data extraction using headless browsers (Playwright), normalizes data into DTOs, and pushes it to the Laravel API.
- **Backend (Laravel):** Receives raw payloads rapidly, saves them, and instantly dispatches events to a database queue.
- **Queue Workers (PHP):** Consume Laravel events to trigger AI scoring, deduplication, price comparisons, and social media broadcasting.
- **Database (MySQL):** Stores deals, logs event lifecycles in `deal_events`, and powers the frontend.
