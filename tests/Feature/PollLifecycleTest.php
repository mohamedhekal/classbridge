<?php

declare(strict_types=1);

use Hekal\ClassBridge\DTOs\CreateSessionData;
use Hekal\ClassBridge\Enums\ParticipantRole;
use Hekal\ClassBridge\Events\PollStarted;
use Hekal\ClassBridge\Exceptions\ClassBridgeException;
use Hekal\ClassBridge\Facades\ClassBridge;
use Hekal\ClassBridge\Models\SessionParticipant;
use Illuminate\Support\Facades\Event;

it('runs the full poll lifecycle', function () {
    Event::fake([PollStarted::class]);

    $session = ClassBridge::session()->create(new CreateSessionData(title: 'Poll lifecycle'));

    ClassBridge::session()->schedule($session, now());
    ClassBridge::session()->openWaiting($session->fresh());
    ClassBridge::session()->start($session->fresh());

    $poll = ClassBridge::polls()->createPoll($session, 'Favorite language?', ['PHP', 'Python', 'Rust']);

    expect($poll->status)->toBe('draft')
        ->and($poll->options)->toHaveCount(3);

    $started = ClassBridge::polls()->start($poll);
    expect($started->status)->toBe('active')
        ->and($started->started_at)->not->toBeNull();

    Event::assertDispatched(PollStarted::class);

    $student = SessionParticipant::query()->create([
        'session_id' => $session->id,
        'role' => ParticipantRole::Student,
        'display_name' => 'Student One',
        'status' => 'joined',
    ]);

    $optionId = $poll->options->first()->id;

    ClassBridge::polls()->respond($started, $student, $optionId);

    $results = ClassBridge::polls()->results($started->fresh());
    expect($results['total_responses'])->toBe(1)
        ->and($results['options'][0]['count'])->toBe(1);

    $closed = ClassBridge::polls()->close($started);
    expect($closed->status)->toBe('closed')
        ->and($closed->ended_at)->not->toBeNull();
});

it('prevents responding to inactive polls', function () {
    $session = ClassBridge::session()->create(new CreateSessionData(title: 'Inactive poll'));

    $poll = ClassBridge::polls()->createPoll($session, 'Question?', ['A', 'B']);

    $student = SessionParticipant::query()->create([
        'session_id' => $session->id,
        'role' => ParticipantRole::Student,
        'display_name' => 'Student',
        'status' => 'joined',
    ]);

    ClassBridge::polls()->respond($poll, $student, $poll->options->first()->id);
})->throws(ClassBridgeException::class);
