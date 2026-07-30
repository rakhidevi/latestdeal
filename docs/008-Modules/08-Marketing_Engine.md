# Marketing Platform Architecture v1.0

This document serves as the living architecture blueprint for the LatestDeal Marketing Center. It outlines the foundational design principles, module boundaries, and extension points that allow the system to scale from an email campaign tool into an omnichannel, AI-driven marketing platform.

## 1. Core Principles

- **Service-Layer First**: Contains all business logic.
- **Livewire UI**: Used only for presentation and interaction.
- **Event-Driven**: All marketing lifecycle events emit Domain Events.
- **Plugin-Based**: Marketing channels are registered dynamically.
- **Capability-Based**: Authorization uses granular capabilities (`marketing.launch`), not just roles.
- **Separation of Concerns**: Theme engine separated from template engine.
- **Queue-First Execution**: All heavy processing is offloaded to queues.
- **AI Extension Points**: Implemented strictly through interfaces.
- **Feature Flags**: Experimental modules are guarded safely.
- **Versioned APIs**: API endpoints follow `/api/v1/marketing/*`.

---

## 2. Module Dependency Rules

To prevent architectural drift, the following flow is strictly enforced:

```text
Admin UI (Blade)
        │
        ▼
Livewire Components
        │
        ▼
Services
        │
        ▼
Repositories
        │
        ▼
Models
```

**Forbidden Dependencies:**
- ❌ `Livewire` → `Models` (Pass DTOs or primitives instead)
- ❌ `Blade` → `Services`
- ❌ `Controllers` → `Queue`
- ❌ `Controllers` → `Mail`
- ❌ `Views` → `Database`
- ❌ `Jobs` → `Views`

---

## 3. Folder Structure & Namespaces

```text
app/
├── Contracts/
│   ├── MarketingChannelInterface.php
│   ├── ThemeContract.php
│   └── ExtensionHookInterface.php
├── Events/
│   └── Marketing/
│       ├── CampaignCreated.php
│       └── ...
├── Livewire/
│   └── Admin/
│       └── Marketing/
├── Services/
│   └── Marketing/
│       ├── Channels/
│       └── CampaignService.php
└── Providers/
    └── MarketingServiceProvider.php
```

---

## 4. Coding Standards

For maximum consistency across the Marketing Engine:

- **Controllers**: Thin (Only handle HTTP routing/auth).
- **Livewire**: UI State only (Forms, Validation, Rendering).
- **Services**: Pure business logic and transaction management.
- **Repositories**: Data access and complex queries.
- **Events**: Domain only (Data payload representation).
- **Listeners**: Side effects (Queueing, Notifications).
- **Jobs**: Long-running background work.
- **Policies**: Authorization logic.
- **DTOs**: Input/Output validation layers.
- **Value Objects**: Immutable data representations.

---

## 5. Event Catalog

| Event | Trigger | Consumers |
|---|---|---|
| `CampaignCreated` | Campaign is saved to DB | Activity Feed, Audit Log |
| `CampaignScheduled` | User sets a schedule | Queue Scheduler |
| `CampaignStarted` | First job dispatched | Dashboard, Notifications |
| `CampaignPaused` | Admin hits pause | Queue Monitor |
| `CampaignCompleted` | Last recipient processed | Analytics, Notifications |
| `CampaignFailed` | Fatal campaign error | Dashboard, Alerts |
| `TemplatePublished` | Template updated | Version History |
| `SettingsUpdated` | Config changed | Cache Refresh, Activity Feed |

---

## 6. Service Contracts

| Service | Responsibility |
|---|---|
| **CampaignService** | Lifecycle management, transition validation. |
| **AudienceService** | Recipient resolution, suppression checks. |
| **TemplateService** | Variable injection and HTML rendering. |
| **ThemeService** | Applies styling contracts to templates. |
| **QueueMonitorService** | Gathers operational metrics and throughput. |
| **NotificationService** | Internal administrator alerts. |
| **ActivityFeedService** | Timeline aggregation across modules. |
| **HealthService** | Platform health checks (Workers, DB, Mail). |

---

## 7. Plugin Manifest & Channel Registration

Plugins declare their capabilities to the `ChannelRegistry`. This allows the UI to dynamically enable features.

**Example Plugin Manifest (Email Channel):**
```json
{
  "channel": "Email",
  "version": "1.0",
  "capabilities": [
    "Scheduling",
    "Preview",
    "Attachments",
    "Tracking",
    "Templates"
  ],
  "status": "Enabled"
}
```

---

## 8. Database & Queues

- **`email_campaigns`**, **`campaign_recipients`**, **`marketing_themes`**, **`marketing_activity_logs`**
- **Queue Topology**: `critical` (Resets), `high` (Alerts), `medium` (Marketing), `low` (Calculations).
