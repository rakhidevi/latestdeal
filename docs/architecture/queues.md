# Queue Management

## Priority Segregation
Workloads are separated by priority to prevent low-priority tasks (SEO generation) from delaying high-priority tasks (Flash deals).

### Priority Levels
1. **HIGH:** Flash deals, Lightning deals, Price changes.
2. **MEDIUM:** New products, Normal deal ingestion.
3. **LOW:** SEO generation, Old price refreshes, Image optimization.
4. **RETRY:** Failed jobs backing off.
5. **DEAD_LETTER:** Permanently failed jobs awaiting manual review.

## Python Dispatcher
```
worker/new/queue/
  dispatcher.py
  priority.py
  retry.py
  dead_letter.py
  jobs.py
```
The dispatcher polls the upstream sources and assigns work to the appropriate priority lanes.
