<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Events;

use Hekal\ClassBridge\Models\Poll;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class PollStarted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Poll $poll,
    ) {}
}
