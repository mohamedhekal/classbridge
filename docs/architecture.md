# Architecture

ClassBridge is a Laravel package that orchestrates live classroom sessions while delegating realtime media to pluggable providers (LiveKit, etc.).

## Layers

```
┌─────────────────────────────────────────────┐
│  HTTP API (optional) / Application code     │
├─────────────────────────────────────────────┤
│  Services: Session, WaitingRoom, Moderation│
│            Attendance, Recording, Tokens    │
├─────────────────────────────────────────────┤
│  ClassBridgeManager + ClassroomProvider     │
├─────────────────────────────────────────────┤
│  Eloquent models + migrations               │
└─────────────────────────────────────────────┘
```

## Session lifecycle

Sessions move through a strict state machine:

`draft → scheduled → waiting → ready → live ⇄ paused → ended → processing → published`

Invalid transitions raise `InvalidStateTransitionException`.

## Provider abstraction

`ClassroomProvider` defines room creation, join tokens, permission updates, moderation calls, and recording controls. The built-in `FakeClassroomDriver` stores state in memory for tests and local development.

Register custom drivers:

```php
ClassBridge::extend('livekit', fn ($app, $config) => new LiveKitDriver($config));
```

## Multi-tenancy

`ClassBridge::forTenant($id)` returns a scoped manager. When enabled, `SessionService::find()` filters by tenant.

## Events

Domain events (`SessionCreated`, `ParticipantJoined`, `RecordingReady`, etc.) allow host applications to sync analytics, notifications, and LMS gradebooks.

## Phase 3 models

Polls, breakout rooms, and whiteboards are migrated and modeled for future services without breaking existing APIs.
