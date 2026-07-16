<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Events;

use Hekal\ClassBridge\Models\ClassroomSession;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class SessionEnded
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ClassroomSession $session,
    ) {}
}
