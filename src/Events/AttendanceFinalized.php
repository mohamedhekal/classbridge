<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Events;

use Hekal\ClassBridge\Models\ClassroomSession;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class AttendanceFinalized
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  array<string, string>  $results
     */
    public function __construct(
        public readonly ClassroomSession $session,
        public readonly array $results,
    ) {}
}
