<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Enums;

enum ParticipantStatus: string
{
    case Invited = 'invited';
    case Waiting = 'waiting';
    case Admitted = 'admitted';
    case Joined = 'joined';
    case Left = 'left';
    case Removed = 'removed';
    case Banned = 'banned';
}
