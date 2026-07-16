<?php

declare(strict_types=1);

use Hekal\ClassBridge\DTOs\CreateSessionData;
use Hekal\ClassBridge\Enums\ParticipantRole;
use Hekal\ClassBridge\Facades\ClassBridge;
use Hekal\ClassBridge\Models\BreakoutParticipant;
use Hekal\ClassBridge\Models\BreakoutRoom;
use Hekal\ClassBridge\Models\Message;
use Hekal\ClassBridge\Models\SessionParticipant;

it('creates and opens breakout rooms with participant assignment', function () {
    $session = ClassBridge::session()->create(new CreateSessionData(title: 'Breakout test'));
    ClassBridge::session()->schedule($session, now());
    ClassBridge::session()->openWaiting($session->fresh());
    ClassBridge::session()->start($session->fresh());

    $rooms = ClassBridge::breakouts()->createRooms($session, ['Team A', 'Team B']);

    expect($rooms)->toHaveCount(2)
        ->and($rooms[0]->provider_room_id)->not->toBeNull();

    $students = collect([
        SessionParticipant::query()->create([
            'session_id' => $session->id,
            'role' => ParticipantRole::Student,
            'display_name' => 'Student 1',
            'status' => 'joined',
        ]),
        SessionParticipant::query()->create([
            'session_id' => $session->id,
            'role' => ParticipantRole::Student,
            'display_name' => 'Student 2',
            'status' => 'joined',
        ]),
    ]);

    ClassBridge::breakouts()->assignParticipants($session, [
        $rooms[0]->id => [$students[0]->id],
        $rooms[1]->id => [$students[1]->id],
    ]);

    expect(BreakoutParticipant::query()->count())->toBe(2);

    $opened = ClassBridge::breakouts()->open($session);
    expect($opened[0]->status)->toBe('open');

    $teacher = $session->participants()->where('role', ParticipantRole::Teacher)->first();

    $message = ClassBridge::breakouts()->broadcastMessage($session, $teacher, 'Five minutes left!');
    expect($message)->toBeInstanceOf(Message::class)
        ->and($message->type)->toBe('breakout_broadcast');

    $closed = ClassBridge::breakouts()->closeAll($session);
    expect($closed)->toBe(2)
        ->and(BreakoutRoom::query()->where('status', 'closed')->count())->toBe(2);
});

it('assigns participants randomly across rooms', function () {
    $session = ClassBridge::session()->create(new CreateSessionData(title: 'Random breakout'));

    ClassBridge::breakouts()->createRooms($session, ['Room 1', 'Room 2']);

    foreach (range(1, 4) as $index) {
        SessionParticipant::query()->create([
            'session_id' => $session->id,
            'role' => ParticipantRole::Student,
            'display_name' => "Student {$index}",
            'status' => 'joined',
        ]);
    }

    ClassBridge::breakouts()->assignParticipants($session, [], 'random');

    expect(BreakoutParticipant::query()->count())->toBe(4);
});
