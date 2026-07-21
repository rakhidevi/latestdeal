# AI Architecture

## Segmented Capabilities
To support future AI features without bloating a single file, the AI capabilities are strictly segmented:

```
worker/new/ai/
  ranking/        # Value scoring, Deal scoring
  captions/       # Generating social media and SEO copy
  embeddings/     # Generating vector embeddings for Semantic Deduplication
  classification/ # Auto-categorization of raw deals
  summaries/      # Product summaries and highlight extraction
  recommendations/# User-specific deal recommendations
```

## Immutable Outputs
AI responses must be parsed and returned as DTOs (e.g., `ScoreDTO`), preventing parsing errors downstream.

## Feature Flags
All AI capabilities are wrapped in feature flags (e.g., `ai.scoring.enabled=true`). If disabled or if the LLM provider is down, the system gracefully degrades (e.g., falling back to rule-based ranking or generic captions).
