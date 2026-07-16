<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Exceptions;

use Hekal\ClassBridge\Enums\SessionStatus;

final class InvalidStateTransitionException extends ClassBridgeException
{
    public static function from(SessionStatus $from, SessionStatus $to): self
    {
        return new self("Cannot transition session from [{$from->value}] to [{$to->value}].");
    }
}
