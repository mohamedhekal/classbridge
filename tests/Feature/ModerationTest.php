<?php

declare(strict_types=1);

use Hekal\ClassBridge\DTOs\CreateSessionData;
use Hekal\ClassBridge\Enums\MediaType;
use Hekal\ClassBridge\Enums\ParticipantRole;
use Hekal\ClassBridge\Enums\ParticipantStatus;
use Hekal\ClassBridge\Events\ParticipantMuted;
use Hekal\ClassBridge\Exceptions\UnauthorizedClassroomActionException;
use Hekal\ClassBridge\Facades\ClassBridge;
use Hekal\ClassBridge\Models\SessionParticipant;
use Illuminate\Support\Facades\Event;

it('allows teachers to mute students', function () {
    Event::fake([ParticipantMuted::class]);

    $session = ClassBridge::session()->create(new CreateSessionData(title: 'Moderation test'));
    $teacher = $session->participants->first();

    $student = SessionParticipant::query()->create([
        'session_id' => $session->id,
        'role' => ParticipantRole::Student,
        'display_name' => 'Student',
        'status' => ParticipantStatus::Joined,
        'can_publish_audio' => true,
        'provider_participant_id' => 'student-provider-id',
    ]);

    ClassBridge::moderation()->mute($session, $teacher, $student, MediaType::Audio);

    expect($student->fresh()->can_publish_audio)->toBeFalse();

    Event::assertDispatched(ParticipantMuted::class);
});

it('prevents students from moderating', function () {
    $session = ClassBridge::session()->create(new CreateSessionData(title: 'Unauthorized test'));

    $studentA = SessionParticipant::query()->create([
        'session_id' => $session->id,
        'role' => ParticipantRole::Student,
        'display_name' => 'Student A',
        'status' => ParticipantStatus::Joined,
    ]);

    $studentB = SessionParticipant::query()->create([
        'session_id' => $session->id,
        'role' => ParticipantRole::Student,
        'display_name' => 'Student B',
        'status' => ParticipantStatus::Joined,
    ]);

    ClassBridge::moderation()->mute($session, $studentA, $studentB, MediaType::Audio);
})->throws(UnauthorizedClassroomActionException::class);

it('locks sessions for teachers', function () {
    $session = ClassBridge::session()->create(new CreateSessionData(title: 'Lock test'));
    $teacher = $session->participants->first();

    ClassBridge::moderation()->lock($session, $teacher);

    expect($session->fresh()->locked_at)->not->toBeNull();
});
