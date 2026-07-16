<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Exceptions;

final class UnauthorizedClassroomActionException extends ClassBridgeException
{
    public static function forAction(string $action): self
    {
        return new self("You are not authorized to perform [{$action}] in this classroom.");
    }
}
