<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Enums;

enum SessionStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Waiting = 'waiting';
    case Ready = 'ready';
    case Live = 'live';
    case Paused = 'paused';
    case Ended = 'ended';
    case Processing = 'processing';
    case Published = 'published';
    case Cancelled = 'cancelled';
    case Failed = 'failed';

    /**
     * @return list<SessionStatus>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Scheduled, self::Cancelled],
            self::Scheduled => [self::Waiting, self::Ready, self::Live, self::Cancelled],
            self::Waiting => [self::Ready, self::Live, self::Cancelled],
            self::Ready => [self::Live, self::Cancelled],
            self::Live => [self::Paused, self::Ended],
            self::Paused => [self::Live, self::Ended],
            self::Ended => [self::Processing, self::Published],
            self::Processing => [self::Published, self::Failed],
            self::Published, self::Cancelled, self::Failed => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Published, self::Cancelled, self::Failed], true);
    }
}
