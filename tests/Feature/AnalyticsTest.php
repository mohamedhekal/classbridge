<?php

declare(strict_types=1);

use Hekal\ClassBridge\DTOs\CreateSessionData;
use Hekal\ClassBridge\Enums\ParticipantRole;
use Hekal\ClassBridge\Facades\ClassBridge;
use Hekal\ClassBridge\Models\HandRaise;
use Hekal\ClassBridge\Models\Message;
use Hekal\ClassBridge\Models\SessionParticipant;

it('summarizes session engagement metrics', function () {
    $session = ClassBridge::session()->create(new CreateSessionData(
        title: 'Analytics test',
        startsAt: now()->subHour(),
    ));

    ClassBridge::session()->schedule($session, now()->subHour());
    ClassBridge::session()->start($session->fresh());

    $student = SessionParticipant::query()->create([
        'session_id' => $session->id,
        'role' => ParticipantRole::Student,
        'display_name' => 'Student',
        'status' => 'joined',
    ]);

    ClassBridge::attendance()->record($session, $student, 'joined', now()->subMinutes(50));

    Message::query()->create([
        'session_id' => $session->id,
        'participant_id' => $student->id,
        'body' => 'Hello!',
        'type' => 'chat',
    ]);

    HandRaise::query()->create([
        'session_id' => $session->id,
        'participant_id' => $student->id,
        'raised_at' => now(),
        'status' => 'raised',
    ]);

    $poll = ClassBridge::polls()->createPoll($session, 'Ready?', ['Yes', 'No']);
    ClassBridge::polls()->start($poll);
    ClassBridge::polls()->respond($poll, $student, $poll->options->first()->id);

    ClassBridge::session()->end($session->fresh());

    $summary = ClassBridge::analytics()->sessionSummary($session->fresh());

    expect($summary['attendance_count'])->toBe(1)
        ->and($summary['message_count'])->toBe(1)
        ->and($summary['hand_raise_count'])->toBe(1)
        ->and($summary['poll_response_count'])->toBe(1)
        ->and($summary['duration_minutes'])->not->toBeNull();
});
