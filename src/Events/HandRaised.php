<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Events;

use Hekal\ClassBridge\Models\HandRaise;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class HandRaised
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly HandRaise $handRaise,
    ) {}
}
