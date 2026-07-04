# AI Cost Management

**ID:** REQ-AI-004
**Status:** Completed
**Last Updated:** 2026-06-29

## Cost Controls
- **100% Free Local AI:** To completely eliminate API costs (like OpenAI), the platform utilizes a **Hybrid Worker Model**.
- **Ollama Integration:** Caption generation is offloaded to a local instance of Ollama (running models like `llama3` or `phi3`) on the admin's local machine.
- **Zero Token Fees:** Since all processing is local, there are no token limits, `max_tokens` restrictions, or monthly API bills.
