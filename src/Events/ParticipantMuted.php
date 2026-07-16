<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Events;

use Hekal\ClassBridge\Enums\MediaType;
use Hekal\ClassBridge\Models\SessionParticipant;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class ParticipantMuted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly SessionParticipant $participant,
        public readonly MediaType $media,
    ) {}
}
