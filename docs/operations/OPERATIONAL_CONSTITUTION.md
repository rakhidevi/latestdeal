# Operational Constitution

This document dictates how the Universal Commerce Intelligence Platform behaves in production. Unlike development rules, these 10 laws govern the runtime, the telemetry, and the release lifecycle of the intelligence engine.

---

### Rule 1 — Production Never Learns
Production never changes weights. Production never edits policies. Production never edits knowledge. Learning happens offline and is systematically deployed.

### Rule 2 — Everything Is Versioned
Every single run records the exact versions of the Engine, Strategy, Policy, Knowledge, and Provider used. Forever.

### Rule 3 — Promotion Only
`Development ➔ RC ➔ Certified ➔ Production`
Nothing skips stages. Every Discovery Profile, Strategy, and Policy must strictly move through this chain.

### Rule 4 — Immutable History
Nothing is ever deleted. Entities and configurations are only archived or deprecated.

### Rule 5 — Replay Must Always Work
Every production decision made today must be 100% reproducible years later using the immutable Historical Store.

### Rule 6 — No Hidden Intelligence
No magic numbers. No hidden thresholds. Every weight, budget, and threshold must come from configuration, not code.

### Rule 7 — Telemetry First
If it cannot be measured, it does not exist. Every new feature must be accompanied by relevant metrics, traces, and operational reporting.

### Rule 8 — Safe Failure
If uncertain: Don't publish. The default state for any exception or confidence drop is rejection.

### Rule 9 — Production Is Boring
Production should be predictable and calm. Innovation, breaking changes, and chaos belong in the RC environment.

### Rule 10 — Rollback Is Mandatory
Every deployment, policy update, knowledge change, and strategy toggle must support an instantaneous rollback mechanism.

---

### The 5-Point Feature Audit
From today onward, every new feature must answer "Yes" to these five questions to be considered production-ready:
1. Can it be **traced**?
2. Can it be **replayed**?
3. Can it be **rolled back**?
4. Can it be **measured**?
5. Can it be **versioned**?
