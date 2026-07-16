<?php

declare(strict_types=1);

use Hekal\ClassBridge\DTOs\CreateSessionData;
use Hekal\ClassBridge\Enums\ParticipantRole;
use Hekal\ClassBridge\Enums\ParticipantStatus;
use Hekal\ClassBridge\Events\ParticipantAdmitted;
use Hekal\ClassBridge\Events\ParticipantEnteredWaitingRoom;
use Hekal\ClassBridge\Facades\ClassBridge;
use Hekal\ClassBridge\Models\SessionParticipant;
use Illuminate\Support\Facades\Event;

it('admits participants from the waiting room', function () {
    Event::fake([ParticipantEnteredWaitingRoom::class, ParticipantAdmitted::class]);

    $session = ClassBridge::session()->create(new CreateSessionData(title: 'Waiting room test'));

    $student = SessionParticipant::query()->create([
        'session_id' => $session->id,
        'role' => ParticipantRole::Student,
        'display_name' => 'Student One',
        'status' => ParticipantStatus::Invited,
        'can_publish_audio' => true,
        'can_publish_video' => true,
    ]);

    $entry = ClassBridge::waitingRoom()->enter($session, $student);

    expect($entry->status)->toBe('pending')
        ->and($student->fresh()->status)->toBe(ParticipantStatus::Waiting);

    Event::assertDispatched(ParticipantEnteredWaitingRoom::class);

    ClassBridge::waitingRoom()->admit($session, $entry->fresh());

    expect($entry->fresh()->status)->toBe('admitted')
        ->and($student->fresh()->status)->toBe(ParticipantStatus::Admitted);

    Event::assertDispatched(ParticipantAdmitted::class);
});

it('admits all pending waiting entries', function () {
    $session = ClassBridge::session()->create(new CreateSessionData(title: 'Admit all test'));

    foreach (['Alice', 'Bob'] as $name) {
        $participant = SessionParticipant::query()->create([
            'session_id' => $session->id,
            'role' => ParticipantRole::Student,
            'display_name' => $name,
            'status' => ParticipantStatus::Invited,
        ]);

        ClassBridge::waitingRoom()->enter($session, $participant);
    }

    $count = ClassBridge::waitingRoom()->admitAll($session);

    expect($count)->toBe(2);
});

it('rejects waiting entries', function () {
    $session = ClassBridge::session()->create(new CreateSessionData(title: 'Reject test'));

    $student = SessionParticipant::query()->create([
        'session_id' => $session->id,
        'role' => ParticipantRole::Student,
        'display_name' => 'Rejected Student',
        'status' => ParticipantStatus::Invited,
    ]);

    $entry = ClassBridge::waitingRoom()->enter($session, $student);
    ClassBridge::waitingRoom()->reject($session, $entry);

    expect($entry->fresh()->status)->toBe('rejected')
        ->and($student->fresh()->status)->toBe(ParticipantStatus::Removed);
});
