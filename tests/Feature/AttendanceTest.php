<?php

declare(strict_types=1);

use Hekal\ClassBridge\DTOs\CreateSessionData;
use Hekal\ClassBridge\Enums\AttendanceStatus;
use Hekal\ClassBridge\Enums\ParticipantRole;
use Hekal\ClassBridge\Events\AttendanceFinalized;
use Hekal\ClassBridge\Facades\ClassBridge;
use Hekal\ClassBridge\Models\SessionParticipant;
use Illuminate\Support\Facades\Event;

it('finalizes attendance using configured rules', function () {
    Event::fake([AttendanceFinalized::class]);

    $session = ClassBridge::session()->create(new CreateSessionData(
        title: 'Attendance test',
        startsAt: now()->subHour(),
    ));

    ClassBridge::session()->schedule($session, now()->subHour());
    ClassBridge::session()->start($session->fresh());

    $student = SessionParticipant::query()->create([
        'session_id' => $session->id,
        'role' => ParticipantRole::Student,
        'display_name' => 'Present Student',
        'status' => 'joined',
    ]);

    ClassBridge::attendance()->record($session, $student, 'joined', now()->subMinutes(57));
    ClassBridge::attendance()->record($session, $student, 'left', now()->subMinutes(5));

    ClassBridge::session()->end($session->fresh());

    $results = ClassBridge::attendance()->finalize($session->fresh());

    expect($results[$student->id])->toBe(AttendanceStatus::Present);

    Event::assertDispatched(AttendanceFinalized::class);
});

it('marks absent students without join events', function () {
    $session = ClassBridge::session()->create(new CreateSessionData(title: 'Absent test'));

    $student = SessionParticipant::query()->create([
        'session_id' => $session->id,
        'role' => ParticipantRole::Student,
        'display_name' => 'Absent Student',
        'status' => 'invited',
    ]);

    $results = ClassBridge::attendance()->finalize($session);

    expect($results[$student->id])->toBe(AttendanceStatus::Absent);
});
