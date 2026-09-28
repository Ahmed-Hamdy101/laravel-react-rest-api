# AI Security and Operations

## Data Protection

- Never send passwords, access tokens, CVV, card numbers, or provider secrets to an AI provider.
- Redact email, phone, address, and payment identifiers where possible.
- Store prompt and response metadata without retaining sensitive raw content unnecessarily.
- Keep AI providers replaceable through adapters.

## Authorization

- Enforce user ownership before exposing order or payment context.
- Apply separate permissions to customer and administrator tools.
- Require human confirmation for refunds, cancellations, or account changes.
- Do not let AI modify prices, inventory, roles, or payment state directly.

## Operations

- Apply per-user rate limits.
- Add token and cost tracking.
- Use approved prompt templates.
- Queue non-interactive AI jobs.
- Log correlation IDs and provider latency.
- Monitor failures, retries, usage, and budget limits.
- Add fallback behavior when the provider is unavailable.

## Testing

Test:

- Prompt data redaction.
- Authorization boundaries.
- Prompt injection attempts.
- Invalid or unsafe model output.
- Provider timeout and retry behavior.
- Usage limits.
- Human approval for privileged actions.
