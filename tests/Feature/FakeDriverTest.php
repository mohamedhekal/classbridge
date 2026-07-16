<?php

declare(strict_types=1);

use Hekal\ClassBridge\DTOs\SessionData;
use Hekal\ClassBridge\Enums\SessionStatus;
use Hekal\ClassBridge\Facades\ClassBridge;

it('creates a room with the fake driver', function (): void {
    $room = ClassBridge::driver()->createRoom(new SessionData(
        id: 'session-1',
        title: 'Math 101',
        status: SessionStatus::Draft,
    ));

    expect($room->roomId)->toStartWith('fake-room-')
        ->and($room->provider)->toBe('fake');
});
