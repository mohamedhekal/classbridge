<?php

declare(strict_types=1);

use Hekal\ClassBridge\DTOs\CreateSessionData;
use Hekal\ClassBridge\Enums\SessionStatus;
use Hekal\ClassBridge\Events\SessionCreated;
use Hekal\ClassBridge\Events\SessionEnded;
use Hekal\ClassBridge\Events\SessionScheduled;
use Hekal\ClassBridge\Events\SessionStarted;
use Hekal\ClassBridge\Facades\ClassBridge;
use Illuminate\Support\Facades\Event;

it('runs the full session lifecycle', function () {
    Event::fake([
        SessionCreated::class,
        SessionScheduled::class,
        SessionStarted::class,
        SessionEnded::class,
    ]);

    $session = ClassBridge::session()->create(new CreateSessionData(
        title: 'Intro to Laravel',
        tenantId: 'tenant-a',
        teacherDisplayName: 'Dr. Hekal',
    ));

    expect($session->status)->toBe(SessionStatus::Draft)
        ->and($session->provider_room_id)->not->toBeNull()
        ->and($session->participants)->toHaveCount(1)
        ->and($session->settings)->not->toBeNull();

    Event::assertDispatched(SessionCreated::class);

    ClassBridge::session()->schedule($session, new DateTimeImmutable('+1 hour'));
    expect($session->fresh()->status)->toBe(SessionStatus::Scheduled);
    Event::assertDispatched(SessionScheduled::class);

    ClassBridge::session()->openWaiting($session->fresh());
    expect($session->fresh()->status)->toBe(SessionStatus::Waiting);

    ClassBridge::session()->start($session->fresh());
    expect($session->fresh()->status)->toBe(SessionStatus::Live)
        ->and($session->fresh()->started_at)->not->toBeNull();
    Event::assertDispatched(SessionStarted::class);

    ClassBridge::session()->end($session->fresh());
    expect($session->fresh()->status)->toBe(SessionStatus::Ended)
        ->and($session->fresh()->ended_at)->not->toBeNull();
    Event::assertDispatched(SessionEnded::class);
});

it('exposes join url helpers on created sessions', function () {
    $session = ClassBridge::session()->create(new CreateSessionData(
        title: 'Join URL test',
    ));

    expect($session->joinUrl())->toContain('/classroom/join/');
});
