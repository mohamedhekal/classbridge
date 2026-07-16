<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Services;

use Hekal\ClassBridge\Enums\SessionStatus;
use Hekal\ClassBridge\Exceptions\InvalidStateTransitionException;
use Hekal\ClassBridge\Models\ClassroomSession;

final class SessionStateMachine
{
    public function transition(ClassroomSession $session, SessionStatus $to): ClassroomSession
    {
        $from = $session->status;

        if (! $from->canTransitionTo($to)) {
            throw InvalidStateTransitionException::from($from, $to);
        }

        $session->status = $to;

        return $session;
    }

    public function canTransition(ClassroomSession $session, SessionStatus $to): bool
    {
        return $session->status->canTransitionTo($to);
    }
}
