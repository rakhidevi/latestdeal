# Technology Stack

**ID:** REQ-SRS-001
**Status:** Completed
**Last Updated:** 2026-06-29

## Constraint
The architecture must run on standard MilesWeb PHP hosting (e.g., cPanel/Shared/VPS) while remaining 100% free (open-source), advanced, and highly performant.

## 1. Backend Framework
- **Primary:** PHP 8.2+ with **Laravel 11.x**. 
- **Why?** Laravel is the most advanced PHP framework, offers extremely fast routing, built-in caching, robust database ORM (Eloquent), and elegant API handling. It easily deploys to standard PHP hosting.

## 2. Database & Caching
- **Primary Database:** MySQL 8.x or MariaDB (Standard on MilesWeb).
- **Caching & Queues:** Since Redis might not be available on basic PHP hosting, we will use the **Database Queue Driver** for processing background jobs (like scraping and posting to Telegram). We will use the **File Cache** or **Database Cache** driver to serve pages lightning-fast without needing extra memory services.

## 3. Frontend (User Interface)
- **Engine:** Laravel Blade (Server-Side Rendering for maximum SEO and speed).
- **Styling:** Vanilla CSS or Tailwind CSS (compiled locally, served as static files).
- **Interactivity:** Alpine.js or Vanilla JavaScript. (No heavy React/Node.js SSR requirements, making it perfect and fast for PHP hosting).

## 4. Background Processing & Automation
- **Task Scheduling:** Standard Linux **Cron Jobs** configured via cPanel to trigger Laravel's Task Scheduler (`php artisan schedule:run` every minute). This will handle the automated ingestion of deals and posting to social channels.
