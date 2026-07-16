<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Events;

use Hekal\ClassBridge\Models\SessionParticipant;
use Hekal\ClassBridge\Models\WaitingEntry;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class ParticipantEnteredWaitingRoom
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly WaitingEntry $entry,
        public readonly SessionParticipant $participant,
    ) {}
}
