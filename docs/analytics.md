# Session analytics

ClassBridge exposes engagement metrics via `AnalyticsService::sessionSummary()`.

## Usage

```php
use Hekal\ClassBridge\Facades\ClassBridge;

$summary = ClassBridge::analytics()->sessionSummary($session);
```

## Response shape

| Field | Description |
|-------|-------------|
| `attendance_count` | Distinct participants with a `joined` attendance event |
| `participant_count` | Students/guests enrolled on the session |
| `message_count` | Chat and breakout broadcast messages |
| `hand_raise_count` | Hand raise records |
| `poll_response_count` | Poll answers submitted |
| `duration_minutes` | Live duration (`started_at` → `ended_at` or now) |

## HTTP API

When `CLASSBRIDGE_API=true`:

```
GET /api/classbridge/sessions/{session}/analytics/summary
```

## Extending

Hook `AttendanceFinalized`, `PollStarted`, or custom events to push summaries to your data warehouse. The service reads normalized tables only—no provider SDK calls.

## Related

- [Architecture](architecture.md)
- [Integration guide](integration.md)
