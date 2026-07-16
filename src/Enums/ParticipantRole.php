<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Enums;

enum ParticipantRole: string
{
    case Teacher = 'teacher';
    case Cohost = 'cohost';
    case Student = 'student';
    case Guest = 'guest';
    case Observer = 'observer';

    public function canModerate(): bool
    {
        return in_array($this, [self::Teacher, self::Cohost], true);
    }

    public function canManageSession(): bool
    {
        return $this === self::Teacher;
    }

    public function canPublishByDefault(): bool
    {
        return in_array($this, [self::Teacher, self::Cohost, self::Student], true);
    }
}
