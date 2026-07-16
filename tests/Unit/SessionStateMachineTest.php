<?php

declare(strict_types=1);

use Hekal\ClassBridge\Enums\SessionStatus;
use Hekal\ClassBridge\Exceptions\InvalidStateTransitionException;
use Hekal\ClassBridge\Models\ClassroomSession;
use Hekal\ClassBridge\Services\SessionStateMachine;

it('allows valid session transitions', function () {
    $machine = new SessionStateMachine;

    $session = new ClassroomSession(['status' => SessionStatus::Draft]);
    $machine->transition($session, SessionStatus::Scheduled);

    expect($session->status)->toBe(SessionStatus::Scheduled);
});

it('rejects invalid session transitions', function () {
    $machine = new SessionStateMachine;

    $session = new ClassroomSession(['status' => SessionStatus::Draft]);

    $machine->transition($session, SessionStatus::Live);
})->throws(InvalidStateTransitionException::class);

it('defines lifecycle from draft to ended', function () {
    $machine = new SessionStateMachine;

    $session = ClassroomSession::query()->create([
        'title' => 'State machine test',
        'status' => SessionStatus::Draft,
        'provider' => 'fake',
    ]);

    $machine->transition($session, SessionStatus::Scheduled);
    $machine->transition($session, SessionStatus::Waiting);
    $machine->transition($session, SessionStatus::Live);
    $machine->transition($session, SessionStatus::Ended);

    expect($session->status)->toBe(SessionStatus::Ended);
});

it('marks terminal states correctly', function () {
    expect(SessionStatus::Published->isTerminal())->toBeTrue();
    expect(SessionStatus::Live->isTerminal())->toBeFalse();
});
