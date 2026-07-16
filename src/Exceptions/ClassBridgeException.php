<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Exceptions;

use RuntimeException;

class ClassBridgeException extends RuntimeException
{
    public static function unknownDriver(string $name): self
    {
        return new self("ClassBridge driver [{$name}] is not configured.");
    }

    public static function unsupportedDriver(string $type): self
    {
        return new self("ClassBridge driver type [{$type}] is not supported.");
    }

    public static function sessionNotFound(string $id): self
    {
        return new self("Classroom session [{$id}] was not found.");
    }

    public static function participantNotFound(string $id): self
    {
        return new self("Session participant [{$id}] was not found.");
    }

    public static function invalidToken(string $reason = 'Invalid or expired join token.'): self
    {
        return new self($reason);
    }

    public static function roomNotFound(string $roomId): self
    {
        return new self("Provider room [{$roomId}] was not found.");
    }

    public static function invalidPollState(string $reason): self
    {
        return new self($reason);
    }

    public static function pollOptionNotFound(int|string $optionId): self
    {
        return new self("Poll option [{$optionId}] was not found.");
    }

    public static function invalidRecordingState(string $reason): self
    {
        return new self($reason);
    }
}
