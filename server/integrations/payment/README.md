# Payment Plan

The payment implementation plan is organized here. The detailed documents remain at the repository root for compatibility with the original roadmap.

## Documents

- [Implementation Tasks](TASKS.md)
- [Payment Domain](../04-payment-domain.md)
- [Payment Provider Integration](../05-provider-integration.md)
- [Order and Payment Workflow](../06-order-payment-workflow.md)
- [Payment Testing](../10-testing-strategy.md)
- [Payment Operations](../09-observability-and-operations.md)

## Implementation Order

```text
Payment domain and migrations
    -> Provider-independent contracts
    -> One provider in sandbox
    -> Webhooks and idempotency
    -> Order/payment integration
    -> Refunds and reconciliation
    -> Additional providers
```
