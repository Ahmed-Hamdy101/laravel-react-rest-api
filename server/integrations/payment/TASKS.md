# Payment Implementation Tasks

Use this checklist to implement payment safely in the Laravel API. Complete tasks in order unless a task is explicitly marked parallel.

## IMP-01: Baseline and Decisions

- [ ] Confirm the current test suite passes.
- [ ] Document the existing order, product, user, and authorization behavior.
- [ ] Decide the first payment provider and supported currency.
- [ ] Decide whether checkout is hosted by the provider or tokenized in the client.
- [ ] Record provider, webhook, refund, and data-retention decisions.
- [ ] Define local, sandbox, staging, and production environment variables.

## IMP-02: Payment Domain

- [ ] Create the Payment module boundaries under `app/Modules/Payment`.
- [ ] Define payment and refund status enums.
- [ ] Define money and payment state-transition rules.
- [ ] Create the `payments` migration.
- [ ] Create the `payment_attempts` migration.
- [ ] Create the `payment_webhooks` migration.
- [ ] Add foreign keys, indexes, and unique idempotency constraints.
- [ ] Add Eloquent models, relationships, and API resources.
- [ ] Add unit tests for valid and invalid state transitions.

## IMP-03: Order Integration

- [ ] Add server-side order creation if it is not already available.
- [ ] Calculate totals from trusted product data.
- [ ] Freeze the payable order amount and currency.
- [ ] Prevent payment for cancelled, already-paid, or invalid orders.
- [ ] Add order ownership and administrator authorization policies.
- [ ] Define the order-to-payment state transition contract.
- [ ] Add feature tests for order and payment authorization.

## IMP-04: Provider Contract

- [ ] Define the provider-independent payment interface.
- [ ] Create DTOs for create, confirm, status, cancel, refund, and webhook operations.
- [ ] Create a provider registry or factory.
- [ ] Map provider statuses to internal statuses.
- [ ] Add a fake provider for automated tests.
- [ ] Ensure controllers do not call provider SDKs directly.

## IMP-05: Sandbox Provider

- [ ] Add provider configuration without committing secrets.
- [ ] Implement payment creation.
- [ ] Implement confirmation or hosted checkout redirect.
- [ ] Implement payment status lookup.
- [ ] Implement cancellation.
- [ ] Implement full and partial refunds.
- [ ] Add provider timeout and error translation.
- [ ] Add provider contract tests.

## IMP-06: API and Idempotency

- [ ] Add `POST /api/v1/orders/{order}/payments`.
- [ ] Add `GET /api/v1/payments/{payment}`.
- [ ] Add confirm and cancel endpoints where required by the provider.
- [ ] Add refund endpoints restricted to authorized users.
- [ ] Require an idempotency key for payment creation and refunds.
- [ ] Return the original result for a repeated idempotency key.
- [ ] Add rate limits for payment and refund endpoints.
- [ ] Document request, response, and error contracts.

## IMP-07: Webhooks and Reliability

- [ ] Add a public provider webhook endpoint.
- [ ] Verify the provider signature using the raw request body.
- [ ] Verify provider amount, currency, and transaction reference.
- [ ] Store webhook event IDs before processing.
- [ ] Make duplicate and out-of-order webhooks harmless.
- [ ] Queue webhook processing in production.
- [ ] Add retry and failed-job handling.
- [ ] Add correlation IDs to requests, jobs, logs, and payment records.

## IMP-08: Security and Compliance

- [ ] Never store card numbers, CVV, or full banking credentials.
- [ ] Redact provider secrets and payment data from logs.
- [ ] Audit payment, refund, and administrator actions.
- [ ] Review HTTPS, secret rotation, and environment configuration.
- [ ] Add negative tests for unauthorized payment and refund access.
- [ ] Review provider PCI and data-processing responsibilities.

## IMP-09: Reconciliation and Operations

- [ ] Add a scheduled payment reconciliation job.
- [ ] Compare local payment state with provider state.
- [ ] Define handling for unmatched, delayed, and disputed payments.
- [ ] Add payment success, failure, timeout, duplicate, refund, and webhook metrics.
- [ ] Add alerts for webhook backlog, retry storms, and unusual refunds.
- [ ] Document an operational runbook for payment incidents.

## IMP-10: Definition of Done

- [ ] Successful, failed, cancelled, timeout, duplicate, refund, and partial-refund flows are tested.
- [ ] Invalid signatures and amount mismatches are rejected.
- [ ] Duplicate requests and callbacks do not create duplicate charges.
- [ ] Provider secrets are externalized.
- [ ] Payment failures are traceable from request to final state.
- [ ] Sandbox end-to-end flow passes.
- [ ] Rollback and recovery steps are documented.

## Later Providers

- [ ] Add PayPal adapter.
- [ ] Add card processor adapter if different from the first provider.
- [ ] Add Vodafone Cash adapter.
- [ ] Add InstaPay adapter.
- [ ] Run the full provider contract test suite for every adapter.
