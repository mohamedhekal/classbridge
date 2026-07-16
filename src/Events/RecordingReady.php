<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Events;

use Hekal\ClassBridge\Models\Recording;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class RecordingReady
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Recording $recording,
    ) {}
}
