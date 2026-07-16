# Failure testing (LiveKit)

Notes for load and failure scenarios when running ClassBridge against LiveKit.

## Prerequisites

- LiveKit server or Cloud project with API key/secret
- `mohamedhekal/classbridge-livekit` installed and configured
- Webhook endpoint reachable from LiveKit (use ngrok in local dev)

## Load testing

1. Spin up multiple concurrent sessions via `ClassBridge::session()->create()` using unique tenant IDs.
2. Join each room with the fake driver first to validate Laravel-side throughput, then repeat against LiveKit.
3. Monitor:
   - PHP-FPM worker saturation
   - DB connection pool (session/participant writes)
   - LiveKit room creation rate limits

## Failure injection

| Scenario | How to simulate | Expected ClassBridge behavior |
|----------|-----------------|-------------------------------|
| Provider unreachable | Point `LIVEKIT_URL` to invalid host | Session create throws; no orphan DB session if wrapped in transaction |
| Webhook delay | Pause webhook worker | Recordings stay `processing` until `publishRecording()` or webhook catch-up |
| Duplicate webhooks | Replay same payload | Idempotent handlers should not double-count attendance |
| Participant storm | 100+ joins in 10s | Waiting room + token validation should rate-limit at app layer |

## Recording pipeline

1. `recording()->start()` → status `recording`
2. `recording()->stop()` → status `processing`
3. Webhook or `recording()->publishRecording()` → status `ready` + URL

Verify each transition in staging before production cutover.

## Recommended tooling

- `k6` or `artillery` for HTTP API load against `/api/classbridge/sessions`
- LiveKit CLI `lk room list` for room hygiene after tests
- Laravel Telescope or structured logs for webhook tracing
