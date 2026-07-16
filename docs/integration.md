# Integration Guide

## Configuration

Publish and customize `config/classbridge.php`:

```bash
php artisan vendor:publish --tag=classbridge-config
```

Key settings:

| Key | Purpose |
|-----|---------|
| `default` | Active provider driver (`fake`, `livekit`, …) |
| `moderation` | Teacher mute/unmute policies |
| `attendance` | Minimum duration and lateness thresholds |
| `features` | Default feature flags merged per session |
| `tokens` | Join token TTL and signing key |
| `api.enabled` | Expose REST routes |

## Creating sessions

```php
use Hekal\ClassBridge\DTOs\CreateSessionData;
use Hekal\ClassBridge\Facades\ClassBridge;

$session = ClassBridge::forTenant($orgId)->session()->create(new CreateSessionData(
    title: 'Week 3 — Fractions',
    tenantId: $orgId,
    teacherUserType: User::class,
    teacherUserId: $teacher->id,
    features: ['recording' => true, 'polls' => true],
));
```

## Join flow

1. Issue a token: `ClassBridge::joinTokens()->issue($participant)`
2. Share `$token->plainToken` via your join URL template
3. Validate on entry: `ClassBridge::joinTokens()->validate($plain)`
4. Consume one-time tokens: `ClassBridge::joinTokens()->consume($token)`

## Waiting room

```php
ClassBridge::waitingRoom()->enter($session, $participant);
ClassBridge::waitingRoom()->admit($session, $entry);
ClassBridge::waitingRoom()->admitAll($session);
```

## REST API

Enable in config, then use routes under `/api/classbridge`:

- `POST /sessions`
- `POST /sessions/{id}/start`
- `POST /join/validate`
- `POST /webhooks/{provider}`

Protect routes with your app's auth middleware via `classbridge.api.middleware`.

## Webhooks

Set `CLASSBRIDGE_WEBHOOK_SECRET`. Providers should send `X-ClassBridge-Signature: HMAC-SHA256(body)`.

## Custom providers

Implement `Hekal\ClassBridge\Contracts\ClassroomProvider` and register:

```php
use Hekal\ClassBridge\Facades\ClassBridge;

ClassBridge::extend('livekit', function ($app, array $config) {
    return new LiveKitClassroomDriver($config);
});
```

Set `CLASSBRIDGE_DRIVER=livekit`.
